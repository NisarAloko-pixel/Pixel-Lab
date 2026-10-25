<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\AuditLog;
use Illuminate\Auth\Events\{Failed, Login, Logout};
use Illuminate\Support\Facades\Event;

use App\Models\Account;
use App\Models\Advance;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FilamentInventory;
use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\PrintingJob;
use App\Models\Product;
use App\Models\ProductFilament;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\Transaction;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
// SQLite: readers and writers no longer block each other,
        // and a locked database fails after 5s instead of hanging the UI.
        try {
            DB::statement('PRAGMA journal_mode=WAL;');
            DB::statement('PRAGMA busy_timeout=5000;');
            DB::statement('PRAGMA synchronous=NORMAL;');
        } catch (\Throwable $e) {
            // DB not ready yet (first run, build step): ignore
        }

        // Log any request slower than 500ms
        Event::listen(
            \Illuminate\Foundation\Http\Events\RequestHandled::class,
            function ($event) {
                $start = defined('LARAVEL_START') ? LARAVEL_START : ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
                $ms = (int) round((microtime(true) - $start) * 1000);

                if ($ms > 500) {
                    \Illuminate\Support\Facades\Log::info("SLOW {$event->request->method()} /{$event->request->path()} took {$ms}ms");
                }
            }
        );

        // The hidden super admin and the Administrator role bypass every permission check,
        // so a newly added permission is never accidentally invisible to them.
        Gate::before(fn ($user, $ability) => ($user->is_super_admin || $user->hasRole('Administrator')) ? true : null);

        // Audit log for sign-in activity
        Event::listen(Login::class, fn ($e) => AuditLog::create([
            'user_id' => $e->user->id, 'action' => 'login',
        ]));
        Event::listen(Logout::class, fn ($e) => $e->user ? AuditLog::create([
            'user_id' => $e->user->id, 'action' => 'logout',
        ]) : null);
        Event::listen(Failed::class, fn ($e) => AuditLog::create([
            'user_id' => $e->user?->id, 'action' => 'login_failed',
            'new_values' => ['email' => $e->credentials['email'] ?? null],
        ]));

        // Use our custom Pixel Studio branded template instead of Laravel's
        // default Markdown mailable for password-reset emails.
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Reset Your Password')
                ->view('emails.reset-password-email', [
                    'url'  => $url,
                    'user' => $notifiable,
                ]);
        });
    }
}