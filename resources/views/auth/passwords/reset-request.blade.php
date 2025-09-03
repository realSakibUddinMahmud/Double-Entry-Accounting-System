@extends('layouts.app')
@section('title', 'Send OTP')
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
                <form method="POST" action="{{ route('password.reset.request') }}">
                    @csrf
                    <div class="card">
                        <div class="card-body p-5">
                            <div class="login-userheading">
                                <h3>Send OTP</h3>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger text-center" role="alert" style="margin:10px;">
                                    @foreach ($errors->all() as $error)
                                        <p class="mb-0">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control border-end-0 @error('phone') is-invalid @enderror"
                                        name="phone" value="{{ old('phone') }}"
                                        required autocomplete="phone" autofocus>
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
                            <div class="form-login">
                                <button type="submit" class="btn btn-primary w-100">
                                    Send OTP
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
@endsection