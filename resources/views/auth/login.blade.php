@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
<div class="login-wrapper">

    {{--LEFT PANEL--}}
    <aside class="panel-left">
        <div class="panel-left_bg">
            <div class="bg-glow bg-glow--red"></div>
            <div class="bg-glow bg-glow--navy"></div>
            <div class="bg-stripe"></div>
            <div class="bg-dots"></div>
        </div>

        <div class="panel-left_bar-top"></div>

        <div class="panel-left_visual">
            <img src="{{ asset('assets/img/login_page.png') }}" alt="Login Visual" class="login-visual-img">
        </div>

       
    </aside>

    {{--RIGHT PANEL --}}
    <main class="panel-right">
        {{-- Brand --}}
        <div class="brand brand--right">
            <div class="brand_oval">
                <div class="brand_inner"></div>
            </div>
            <img src="{{ asset('assets/img/logo-reka.png') }}" alt="Reka Inka Group" class="brand_logo">
            <div class="brand_text">
                <div class="brand_name">Rekaindo</div>
            </div>
        </div>
            <div class="panel-right_bar-top"></div>

        <div class="login-form-wrap">

            {{-- Header --}}
            <div class="form-header">
                <h2 class="form-header_title">Sistem Informasi Manajemen Aset</h2>
                <p class="form-header_sub">Gunakan akun yang telah terdaftar dalam sistem</p>
            </div>

            {{-- Session Error --}}
            @if (session('error'))
                <div class="alert alert--error" role="alert">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Login Form --}}
            <form class="login-form" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                {{-- NIP --}}
                <div class="field @error('credential') field--error @enderror">
                    <label class="field_label" for="credential">EMAIL / NIP</label>
                    <div class="field_shell">
                        <svg class="field_icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input
                            type="text"
                            id="credential"
                            name="credential"
                            class="field_input"
                            value="{{ old('credential') ?? old('email') }}"
                            placeholder="nama@rekaindo.co.id / NIP"
                            autocomplete="username"
                            autofocus
                            aria-describedby="credential-error"
                        >
                    </div>
                    @error('credential')
                        <p class="field_error" id="credential-error" role="alert">&bull; {{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field @error('password') field--error @enderror">
                    <label class="field_label" for="password">Password</label>
                    <div class="field_shell">
                        <svg class="field_icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="field_input"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            aria-describedby="password-error">
                        <button
                            type="button"
                            class="field_pw-toggle"
                            id="pw-toggle"
                            aria-label="Tampilkan atau sembunyikan password"
                            aria-pressed="false">
                            {{-- Eye open --}}
                            <svg class="icon-eye-open" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            {{-- Eye closed (hidden by default) --}}
                            <svg class="icon-eye-closed" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="field_error" id="password-error" role="alert">&bull; {{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember & Forgot --}}
                <div class="form-row">
                    <label class="checkbox-wrap">
                        <input
                            type="checkbox"
                            name="remember"
                            class="checkbox-wrap_input"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span class="checkbox-wrap_label">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-red">Lupa password?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn--primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span>Masuk</span>
                    <svg class="btn_arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    </svg>
                </button>

                
            </form>


        </div>

    </main>

</div>

@endsection

@push('scripts')
<script>
    (function () {
        const toggle   = document.getElementById('pw-toggle');
        const pwInput  = document.getElementById('password');
        const eyeOpen  = toggle.querySelector('.icon-eye-open');
        const eyeClosed = toggle.querySelector('.icon-eye-closed');

        toggle.addEventListener('click', function () {
            const isHidden = pwInput.type === 'password';
            pwInput.type          = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            eyeOpen.style.display  = isHidden ? 'none'  : '';
            eyeClosed.style.display = isHidden ? ''     : 'none';
        });
    })();
</script>
@endpush