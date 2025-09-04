@extends('layouts.app')
@section('title', 'Login')
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
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="card shadow-sm">
                            <div class="card-body p-4 p-sm-5">
                                <div class="login-userheading text-center mb-4">
                                    <h3 class="fw-bold">Sign In</h3>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="phone" value="{{ old('phone') }}"
                                            class="form-control border-end-0 @error('phone') is-invalid @enderror" required
                                            autocomplete="phone" autofocus placeholder="Enter your phone number">
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
                                        <input type="password" name="password" id="password"
                                            class="pass-input form-control @error('password') is-invalid @enderror" required
                                            autocomplete="current-password" placeholder="Enter your password">
                                        <span class="ti toggle-password ti-eye-off text-gray-9"
                                            onclick="togglePasswordVisibility('password', this)"></span>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-login authentication-check mb-3">
                                    <div class="row">
                                        <div
                                            class="col-12 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                                            <div class="custom-control custom-checkbox">
                                                <label class="checkboxs ps-4 mb-0 pb-0 line-height-1 fs-16 text-gray-6">
                                                    <input type="checkbox" class="form-control" name="remember"
                                                        id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <span class="checkmarks"></span>Remember me
                                                </label>
                                            </div>
                                            <div class="text-start text-sm-end">
                                                @if (Route::has('password.reset.request'))
                                                    <a class="text-orange fs-16 fw-medium"
                                                        href="{{ route('password.reset.request') }}">Forgot Password?</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-login mb-3">
                                    <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
                                </div>
                                <div class="signinform text-center">
                                    <h4 class="mb-0">Don't have account?
                                        <a href="{{ route('register') }}" class="hover-a text-primary"> Create an
                                            account</a>
                                    </h4>
                                </div>
                                {{-- Social login buttons can be enabled if needed --}}
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
