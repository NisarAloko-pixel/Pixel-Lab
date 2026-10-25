<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\AvatarData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill(collect($data)->except('avatar')->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            // Remove the previous file before saving the new one so uploads don't
            // accumulate unused images in storage.
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');

            // Small square copy stored inside the user record, so the photo also
            // reaches other machines through the Firebase sync.
            $user->avatar_data = AvatarData::fromUpload($request->file('avatar'));
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
 * Remove the user's profile photo (not the account).
 */
public function destroyAvatar(Request $request): RedirectResponse
{
    $user = $request->user();

    if ($user->avatar_path) {
        Storage::disk('public')->delete($user->avatar_path);
        Storage::disk('local')->delete($user->avatar_path);
    }

    $user->avatar_path = null;
    $user->avatar_data = null;
    $user->save();

    return Redirect::route('profile.edit')->with('status', 'avatar-removed');
}
}