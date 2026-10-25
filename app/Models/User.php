<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\Auditable;
use Composer\InstalledVersions;
use DiceBear\Avatar;
use DiceBear\Style;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Auditable;

    // is_super_admin is intentionally NOT in here, so no form can ever set it.
    // avatar_data is not fillable either: it is only set by ProfileController and the sync.
    protected $fillable = ["name", "email", "avatar_path", "password", "is_active"];

    protected $hidden = ["password", "remember_token"];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
        "is_active" => "boolean",
        "is_super_admin" => "boolean",          // NEW
    ];

    public function isAdministrator(): bool
    {
        return $this->is_super_admin || $this->hasRole('Administrator');   // CHANGED
    }

    // NEW
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    // NEW: use User::visible() wherever users are listed or counted.
    public function scopeVisible($query)
    {
        return auth()->user()?->is_super_admin
            ? $query
            : $query->where('is_super_admin', false);
    }

    // NEW: anyone else who opens /users/{id}/edit for the super admin gets a 404.
    public function resolveRouteBinding($value, $field = null)
    {
        $user = parent::resolveRouteBinding($value, $field);

        if ($user && $user->is_super_admin && ! auth()->user()?->is_super_admin) {
            return null;
        }

        return $user;
    }

        private function initialAvatar(): string
    {
        // Same seed on every machine (user ids are shared by the sync),
        // so a user gets the same character everywhere.
        $seed = $this->getKey() ? 'pixel-lab-' . $this->getKey() : ($this->name ?: 'user');

        try {
            $svg = Cache::rememberForever('avatar:toon-head:' . md5($seed), function () use ($seed) {
                $basePath = InstalledVersions::getInstallPath('dicebear/styles');
                $style = Style::fromJson(file_get_contents($basePath . '/src/toon-head.json'));

                return (string) new Avatar($style, ['seed' => $seed]);
            });
        } catch (\Throwable $e) {
            // library missing or failed: fall back to the initial-letter avatar
            $initial = e(mb_strtoupper(mb_substr($this->name ?: '?', 0, 1)));
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
                 . '<rect width="100" height="100" fill="#f1e0cc"/>'
                 . '<text x="50" y="50" dy=".35em" text-anchor="middle" '
                 . 'font-family="sans-serif" font-size="44" fill="#a0521d">' . $initial . '</text></svg>';
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

   public function getAvatarUrlAttribute(): string
{
    // 1) Small copy stored in the user record: travels with the Firebase sync,
    //    so the photo shows on every machine.
    if ($this->avatar_data) {
        return $this->avatar_data;
    }

    // 2) The uploaded file on this machine
    if ($this->avatar_path) {
        return route('media', ['path' => $this->avatar_path]);
    }

    // 3) No photo: show the DiceBear toon-head avatar
    //    (falls back to the initial-letter avatar if the library fails)
    return $this->initialAvatar();
}
}