@extends('layouts.app')
@section('title', 'Reset Password')
@section('content')
<div class="login-wrapper login-new">
    <div class="row w-100">
        <div class="col-4 mx-auto">
            <div class="login-content user-login">
                <div class="login-logo">
                    <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="img">
                    <a href="{{ url('/') }}" class="login-logo logo-white">
                        <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Img">
                    </a>
                </div>
                <form method="POST" action="{{ route('password.update.withotp') }}">
                    @csrf
                <div class="card">
                    <div class="card-body p-5">
                        <div class="login-userheading">
                            <h3>{{ __('Reset Password') }}</h3>
                            <h4>Set your new password below.</h4>
                        </div>
                        @if (session('message'))
                            <div class="alert alert-danger text-center" role="alert" style="margin:10px;">
                                {{ session('message') }}
                            </div>
                        @elseif(session('success'))
                            <div class="alert alert-success text-center" role="alert" style="margin:10px;">
                                {{ session('success') }}
                            </div>
                        @elseif(session('error'))
                            <div class="alert alert-danger text-center" role="alert" style="margin:10px;">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger text-center" role="alert" style="margin:10px;">
                                @foreach ($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        
                            <input type="hidden" name="phone" value="{{ $phone }}">

                            <div class="mb-3">
                                <label for="new_password" class="form-label">{{ __('New Password') }} <span class="text-danger">*</span></label>
                                <div class="pass-group">
                                    <input id="new_password" type="password"
                                        class="pass-input form-control @error('new_password') is-invalid @enderror"
                                        name="new_password" required autocomplete="new-password">
                                    <span class="ti toggle-password ti-eye-off text-gray-9" onclick="togglePasswordVisibility('new_password', this)"></span>
                                </div>
                                @error('new_password')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="new_password-confirm" class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                <div class="pass-group">
                                    <input id="new_password-confirm" type="password"
                                        class="pass-input form-control"
                                        name="new_password_confirmation" required autocomplete="new-password">
                                    <span class="ti toggle-password ti-eye-off text-gray-9" onclick="togglePasswordVisibility('new_password-confirm', this)"></span>
                                </div>
                            </div>

                            <div class="form-login">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                            <div class="signinform">
                                <h4>Back to
                                    <a href="{{ route('login') }}" class="hover-a">Sign In</a>
                                </h4>
                            </div>
                        
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
@endsection