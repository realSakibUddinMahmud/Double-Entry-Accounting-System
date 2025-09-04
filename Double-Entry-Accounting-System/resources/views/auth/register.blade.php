@extends('layouts.app')
@section('title', 'Register')
@section('content')

    <div class="login-wrapper login-new">
        <div class="row w-100 min-vh-100 d-flex align-items-center justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                <div class="login-content user-login">
                    <div class="login-logo text-center mb-4">
                        <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="img" class="img-fluid"
                            style="max-height: 80px;">
                        <a href="{{ url('/') }}" class="login-logo logo-white d-none">
                            <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="Img" class="img-fluid">
                        </a>
                    </div>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="card shadow-sm">
                            <div class="card-body p-4 p-sm-5">
                                <div class="login-userheading text-center mb-4">
                                    <h3 class="fw-bold">Sign Up</h3>
                                    <p class="text-muted mb-0">Create your account to access the panel</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input id="name" type="text"
                                            class="form-control border-end-0 @error('name') is-invalid @enderror"
                                            name="name" value="{{ old('name') }}" required autocomplete="name"
                                            autofocus placeholder="Enter your full name">
                                        <span class="input-group-text border-start-0">
                                            <i class="ti ti-user"></i>
                                        </span>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address <span
                                            class="text-muted">(optional)</span></label>
                                    <div class="input-group">
                                        <input id="email" type="email"
                                            class="form-control border-end-0 @error('email') is-invalid @enderror"
                                            name="email" value="{{ old('email') }}" autocomplete="email"
                                            placeholder="Enter your email address">
                                        <span class="input-group-text border-start-0">
                                            <i class="ti ti-mail"></i>
                                        </span>
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control border-end-0 @error('phone') is-invalid @enderror"
                                            name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                                            placeholder="Enter your phone number">
                                        <span class="input-group-text border-start-0">
                                            <i class="ti ti-phone"></i>
                                        </span>
                                    </div>
                                    @error('phone')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="pass-group">
                                        <input id="password" type="password"
                                            class="pass-input form-control @error('password') is-invalid @enderror"
                                            name="password" required autocomplete="new-password"
                                            placeholder="Enter your password">
                                        <span class="ti toggle-password ti-eye-off text-gray-9"
                                            onclick="togglePasswordVisibility('password', this)"></span>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="pass-group">
                                        <input id="password-confirm" type="password" class="pass-input form-control"
                                            name="password_confirmation" required autocomplete="new-password"
                                            placeholder="Confirm your password">
                                        <span class="ti toggle-password ti-eye-off text-gray-9"
                                            onclick="togglePasswordVisibility('password-confirm', this)"></span>
                                    </div>
                                </div>
                                <div class="form-login mb-3">
                                    <button type="submit" class="btn btn-primary w-100 py-2">Sign Up</button>
                                </div>
                                <div class="signinform text-center">
                                    <h4 class="mb-0">Already have an account?
                                        <a href="{{ route('login') }}" class="hover-a text-primary"> Sign In</a>
                                    </h4>
                                </div>
                                {{-- Social register buttons can be enabled if needed --}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, icon) {
            const passwordInput = document.getElementById(inputId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            }
        }
        //  The existing togglePasswordVisibility function works for both password and password-confirm fields
    </script>

    <style>
        @media (max-width: 576px) {
            .login-wrapper {
                padding: 1rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .login-userheading h3 {
                font-size: 1.5rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .signinform h4 {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 375px) {
            .login-wrapper {
                padding: 0.5rem;
            }

            .card-body {
                padding: 1rem !important;
            }
        }
    </style>

@endsection
