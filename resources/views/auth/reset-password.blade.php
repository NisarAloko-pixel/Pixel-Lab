<x-guest-layout>
    <div class="split">

        {{-- ============ LEFT: 3D PRINT SCENE ============ --}}
        <div class="scene-panel">
            <div class="scene-glow"></div>
            <div class="grid-floor"></div>

            <a href="/" class="brand">
                <svg width="30" height="30" viewBox="0 0 32 32" fill="none">
                    <path d="M16 2 L29 9 V23 L16 30 L3 23 V9 Z" stroke="url(#logoGrad)" stroke-width="1.6"/>
                    <path d="M16 2 V16 M16 16 L29 9 M16 16 L3 9 M16 16 V30" stroke="url(#logoGrad)" stroke-width="1.1" opacity="0.55"/>
                    <defs>
                        <linearGradient id="logoGrad" x1="3" y1="2" x2="29" y2="30">
                            <stop stop-color="#12a3b8"/>
                            <stop offset="1" stop-color="#6c5ce7"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="font-display" style="font-size:17px;">{{ config('app.name', 'Pixel Lab') }}</span>
            </a>

            <div class="float-shape" style="top:16%; left:20%; animation-delay:-1s;">
                <svg width="26" height="26" viewBox="0 0 26 26"><path d="M13 1 L25 7 V19 L13 25 L1 19 V7 Z" fill="none" stroke="var(--violet)" stroke-width="1.2" opacity="0.55"/></svg>
            </div>
            <div class="float-shape" style="bottom:22%; left:12%; animation-delay:-3.5s;">
                <svg width="20" height="20" viewBox="0 0 20 20"><rect x="2" y="2" width="16" height="16" rx="3" fill="none" stroke="var(--cyan)" stroke-width="1.2" opacity="0.5"/></svg>
            </div>
            <div class="float-shape" style="top:62%; right:14%; animation-delay:-5s;">
                <svg width="22" height="22" viewBox="0 0 22 22"><circle cx="11" cy="11" r="9" fill="none" stroke="var(--violet)" stroke-width="1.2" opacity="0.45"/><circle cx="11" cy="11" r="3" fill="none" stroke="var(--violet)" stroke-width="1.2" opacity="0.45"/></svg>
            </div>

            <span class="particle" style="width:3px;height:3px; top:30%; left:44%; animation-delay:-1s;"></span>
            <span class="particle" style="width:2px;height:2px; top:52%; left:58%; animation-delay:-3s;"></span>
            <span class="particle" style="width:4px;height:4px; top:40%; left:66%; animation-delay:-5s; background:var(--violet);"></span>
            <span class="particle" style="width:2px;height:2px; top:68%; left:38%; animation-delay:-2.2s;"></span>

            <div class="illustration">
                <svg viewBox="0 0 480 440" style="width:100%; height:auto; overflow:visible;">
                    <defs>
                        <linearGradient id="frame" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#12a3b8" stop-opacity="0.9"/>
                            <stop offset="100%" stop-color="#6c5ce7" stop-opacity="0.7"/>
                        </linearGradient>
                        <linearGradient id="panelGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#12a3b8" stop-opacity="0.07"/>
                            <stop offset="100%" stop-color="#6c5ce7" stop-opacity="0.07"/>
                        </linearGradient>
                    </defs>

                    <!-- floor line -->
                    <line x1="30" y1="334" x2="450" y2="334" stroke="var(--line)" stroke-width="1"/>

                    <!-- enclosure -->
                    <polygon points="110,96 144,76 144,300 110,320" fill="url(#panelGrad)" stroke="url(#frame)" stroke-width="1" opacity="0.35"/>
                    <polygon points="110,96 310,96 344,76 144,76" fill="url(#panelGrad)" stroke="url(#frame)" stroke-width="1.3" opacity="0.75"/>
                    <polygon points="310,96 344,76 344,300 310,320" fill="url(#panelGrad)" stroke="url(#frame)" stroke-width="1.3" opacity="0.75"/>
                    <polygon points="110,320 310,320 344,300 144,300" fill="url(#panelGrad)" stroke="url(#frame)" stroke-width="1.4"/>

                    <!-- corner posts -->
                    <line x1="110" y1="96" x2="110" y2="320" stroke="url(#frame)" stroke-width="1.8"/>
                    <line x1="310" y1="96" x2="310" y2="320" stroke="url(#frame)" stroke-width="1.8"/>
                    <line x1="344" y1="76" x2="344" y2="300" stroke="url(#frame)" stroke-width="1.4" opacity="0.55"/>

                    <!-- open padlock + key icon, representing setting a new password -->
                    <g transform="translate(227,150)">
                        <rect x="-46" y="-8" width="92" height="76" rx="14" fill="#ffffff" stroke="url(#frame)" stroke-width="1.8"/>
                        <path d="M-26,-8 V-30 a26,26 0 0 1 46,-13" fill="none" stroke="var(--success)" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="0" cy="24" r="9" fill="none" stroke="var(--cyan-deep)" stroke-width="4"/>
                        <line x1="0" y1="33" x2="0" y2="48" stroke="var(--cyan-deep)" stroke-width="4" stroke-linecap="round"/>
                        <path d="M22,-46 l14,-14 M32,-40 l10,-10 M40,-32 l8,-8" stroke="var(--success)" stroke-width="3" stroke-linecap="round" opacity="0.7"/>
                    </g>

                    <ellipse cx="220" cy="320" rx="30" ry="5" fill="none" stroke="var(--ink-faint)" stroke-width="1" opacity="0.5"/>

                    <!-- filament spool + bowden tube -->
                    <g transform="translate(344,58)">
                        <line x1="0" y1="18" x2="0" y2="34" stroke="url(#frame)" stroke-width="1.4"/>
                        <g class="spool-spin">
                            <circle r="19" fill="#ffffff" stroke="url(#frame)" stroke-width="1.4"/>
                            <circle r="6" fill="none" stroke="var(--ink-faint)" stroke-width="1"/>
                            <line x1="-19" y1="0" x2="19" y2="0" stroke="var(--ink-faint)" stroke-width="0.6" opacity="0.5"/>
                            <line x1="0" y1="-19" x2="0" y2="19" stroke="var(--ink-faint)" stroke-width="0.6" opacity="0.5"/>
                            <line x1="-13" y1="-13" x2="13" y2="13" stroke="var(--ink-faint)" stroke-width="0.5" opacity="0.35"/>
                            <line x1="13" y1="-13" x2="-13" y2="13" stroke="var(--ink-faint)" stroke-width="0.5" opacity="0.35"/>
                        </g>
                    </g>
                    <path d="M344,92 C344,112 322,110 312,124" fill="none" stroke="var(--violet)" stroke-width="1.4" stroke-dasharray="3 4" opacity="0.75"/>

                    <!-- finished prints on the floor -->
                    <g transform="translate(64,318)" opacity="0.85">
                        <g stroke="var(--violet)" stroke-width="1.2" fill="none">
                            <circle r="13"/>
                            <circle r="4.5"/>
                            <line x1="0" y1="-13" x2="0" y2="-18"/>
                            <line x1="0" y1="13" x2="0" y2="18"/>
                            <line x1="-13" y1="0" x2="-18" y2="0"/>
                            <line x1="13" y1="0" x2="18" y2="0"/>
                            <line x1="-9" y1="-9" x2="-12.5" y2="-12.5"/>
                            <line x1="9" y1="-9" x2="12.5" y2="-12.5"/>
                            <line x1="-9" y1="9" x2="-12.5" y2="12.5"/>
                            <line x1="9" y1="9" x2="12.5" y2="12.5"/>
                        </g>
                    </g>
                    <rect x="382" y="306" width="18" height="18" transform="rotate(20 391 315)" fill="none" stroke="var(--cyan-deep)" stroke-width="1.2" opacity="0.8"/>
                </svg>
            </div>

            <div class="tele-card" style="top:17%; right:7%; width:168px; padding:12px 14px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <span class="tele-label">Account security</span>
                    <span class="tele-dot" style="background:var(--success); box-shadow:0 0 8px var(--success);"></span>
                </div>
                <div class="tele-value" style="font-size:15px;">Identity verified</div>
            </div>

            <div class="tele-card d3" style="bottom:6%; left:6%; width:180px; padding:12px 14px;">
                <div class="tele-label" style="margin-bottom:6px;">Password strength</div>
                <div class="tele-bar-track"><div class="tele-bar-fill" style="width:70%;"></div></div>
            </div>

            <div class="tele-card d4" style="top:10%; left:8%; padding:10px 14px; display:flex; align-items:center; gap:8px;">
                <span class="tele-dot" style="background:var(--cyan); box-shadow:0 0 8px var(--cyan);"></span>
                <span style="font-size:12px; color:var(--ink);">One step from sign in</span>
            </div>

            <p class="scene-caption">
                {{ __('Choose a new password for your account. Make it something strong you haven\'t used before.') }}
            </p>
        </div>

        {{-- ============ RIGHT: RESET PASSWORD FORM ============ --}}
        <div class="form-panel">

            <a href="/" class="brand-mobile">
                <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                    <path d="M16 2 L29 9 V23 L16 30 L3 23 V9 Z" stroke="url(#logoGrad2)" stroke-width="1.6"/>
                    <defs>
                        <linearGradient id="logoGrad2" x1="3" y1="2" x2="29" y2="30">
                            <stop stop-color="#12a3b8"/><stop offset="1" stop-color="#6c5ce7"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="font-display" style="font-size:16px;">{{ config('app.name', 'Pixel Lab') }}</span>
            </a>

            <div class="card-wrap" x-data="{ loading: false, showPassword: false, showConfirm: false }">

                <div class="card">

                    <div class="card-head">
                        <h1 class="font-display">{{ __('Set a new password') }}</h1>
                        <p>{{ __('Your new password must be different from previously used passwords.') }}</p>
                    </div>

                    @if ($errors->any())
                        <div class="banner-error">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>{{ __('Something went wrong. Please check the fields below and try again.') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.store') }}" x-on:submit="loading = true">
                        @csrf

                        {{-- Password Reset Token --}}
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="form-fields">

                            {{-- Email Address --}}
                            <div>
                                <x-input-label for="email" :value="__('Email address')" class="label" />
                                <div class="field-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 6l-10 7L2 6"/><path d="M2 6h20v12H2z"/></svg>
                                    <x-text-input id="email" name="email" type="email" :value="old('email', $request->email)" required autofocus
                                        autocomplete="username" placeholder="you@pixellab.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="error-text" />
                            </div>

                            {{-- New Password --}}
                            <div>
                                <x-input-label for="password" :value="__('New password')" class="label" />
                                <div class="field-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                    <x-text-input id="password" name="password"
                                        x-bind:type="showPassword ? 'text' : 'password'"
                                        required autocomplete="new-password" placeholder="••••••••" />
                                    <button type="button" class="field-toggle" x-on:click="showPassword = !showPassword" aria-label="{{ __('Toggle password visibility') }}">
                                        <svg x-show="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg x-show="showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 4.22-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="error-text" />
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirm new password')" class="label" />
                                <div class="field-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                    <x-text-input id="password_confirmation" name="password_confirmation"
                                        x-bind:type="showConfirm ? 'text' : 'password'"
                                        required autocomplete="new-password" placeholder="••••••••" />
                                    <button type="button" class="field-toggle" x-on:click="showConfirm = !showConfirm" aria-label="{{ __('Toggle password visibility') }}">
                                        <svg x-show="!showConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg x-show="showConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 4.22-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="error-text" />
                            </div>

                            {{-- Submit --}}
                            <button type="submit" class="btn-primary" x-bind:disabled="loading">
                                <template x-if="loading"><span class="spinner"></span></template>
                                <span x-text="loading ? '{{ __('Resetting…') }}' : '{{ __('Reset password') }}'"></span>
                            </button>

                        </div>
                    </form>

                    <div class="below-card">
                        {{ __('Remembered your password?') }}
                        <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
                    </div>

                </div>
                @include('partials.footer')

            </div>
        </div>
    </div>
</x-guest-layout>