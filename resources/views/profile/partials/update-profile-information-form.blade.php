<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Main profile form --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                 class="w-16 h-16 rounded-full object-cover"
                 style="border:1px solid var(--line); background:var(--accent-soft);">

            <div>
                <div class="flex items-center justify-between gap-3">
                    <x-input-label for="avatar" :value="__('Profile photo')" />

                    @if ($user->avatar_path || $user->avatar_data)
                        <button type="button"
                                onclick="document.getElementById('remove-avatar-dialog').showModal()"
                                title="{{ __('Remove profile photo') }}"
                                class="inline-flex items-center gap-1 rounded-md text-xs font-medium"
                                style="background:var(--danger-soft) !important; color:var(--danger) !important; padding:3px 8px; border:0; cursor:pointer;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/>
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                            {{ __('Profile Photo') }}
                        </button>
                    @endif
                </div>

                <input id="avatar" name="avatar" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif

            @if (session('status') === 'avatar-removed')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">{{ __('Photo removed.') }}</p>
            @endif
        </div>
    </form>

    {{-- Confirm dialog: native <dialog>, no Alpine needed. Outside the main form. --}}
    @if ($user->avatar_path || $user->avatar_data)
        <dialog id="remove-avatar-dialog"
                onclick="if (event.target === this) this.close()"
                style="border:0; border-radius:16px; padding:24px; width:92%; max-width:380px;
                       background:var(--surface); color:var(--ink); box-shadow:0 20px 50px rgba(0,0,0,.3);">
            <h3 style="font-size:1.05rem; font-weight:600; margin:0 0 6px;">{{ __('Remove profile photo?') }}</h3>
            <p style="font-size:.875rem; color:var(--ink-soft); margin:0 0 20px;">
                {{ __('Your photo will be deleted and a generated avatar will be shown instead.') }}
            </p>

            <form method="POST" action="{{ route('profile.avatar.destroy') }}"
                  style="display:flex; justify-content:flex-end; gap:10px; margin:0;">
                @csrf
                @method('DELETE')

                <button type="button"
                        onclick="document.getElementById('remove-avatar-dialog').close()"
                        style="padding:8px 16px; border-radius:10px; border:1px solid var(--line); background:var(--paper) !important;
                               color:var(--ink) !important; font-size:.875rem; cursor:pointer;">
                    {{ __('Cancel') }}
                </button>

                <button type="submit"
                        style="padding:8px 16px; border-radius:10px; border:0; background:#dc2626 !important;
                               color:#fff !important; font-size:.875rem; font-weight:600; cursor:pointer;">
                    {{ __('Remove') }}
                </button>
            </form>
        </dialog>

        <style>
            #remove-avatar-dialog::backdrop { background: rgba(0,0,0,.5); }
        </style>
    @endif
</section>