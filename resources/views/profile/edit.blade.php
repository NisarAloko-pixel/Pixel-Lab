<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url()->previous() }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to dashboard
        </a>

        <h1 class="ledger-font text-3xl mb-1">Profile</h1>
        <p class="text-sm mb-8" style="color:var(--ink-soft);">Manage your account details and security.</p>

        <div class="card-3d p-8 relative">


            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

                {{-- Profile information --}}
                <div class="md:pr-10" style="border-right:1px solid var(--line);">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="section-icon" style="background:var(--accent-soft); color:var(--accent-ink);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-sm">Profile information</h2>
                            <p class="text-xs" style="color:var(--ink-soft);">Update your name and email address.</p>
                        </div>
                    </div>
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Password --}}
                <div class="md:pl-10">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="section-icon" style="background:var(--success-soft); color:var(--success);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-sm">Update password</h2>
                            <p class="text-xs" style="color:var(--ink-soft);">Use a long, random password to stay secure.</p>
                        </div>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>

            </div>
        </div>
    </div>
</div>
</x-layouts.pixel>