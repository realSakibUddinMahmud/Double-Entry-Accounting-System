@extends('layouts.app')
@section('title', 'Email Verification')
@section('content')

<div class="login-wrapper login-new">
    <div class="row w-100 min-vh-100 d-flex align-items-center justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="login-content user-login">
                <div class="login-logo text-center mb-4">
                    <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="img" class="img-fluid" style="max-height: 80px;">
                    <a href="{{ url('/') }}" class="login-logo logo-white d-none">
                        <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="Img" class="img-fluid">
                    </a>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-sm-5">
                        <div class="login-userheading text-center mb-4">
                            <h3 class="fw-bold">{{ __('Verify Your Email Address') }}</h3>
                            <p class="text-muted mb-0">Check your email for verification link</p>
                        </div>
                        
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                <i class="ti ti-check-circle me-2"></i>
                                {{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endif

                        <div class="text-center mb-4">
                            <div class="verification-icon mb-3">
                                <i class="ti ti-mail-check" style="font-size: 3rem; color: #6c757d;"></i>
                            </div>
                            <p class="mb-3">
                                {{ __('Before proceeding, please check your email for a verification link.') }}
                            </p>
                            <p class="text-muted mb-0">
                                {{ __('If you did not receive the email') }},
                            </p>
                        </div>

                        <form method="POST" action="{{ route('verification.resend') }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="ti ti-refresh me-2"></i>
                                {{ __('Resend Verification Email') }}
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="hover-a text-primary fs-6">
                                <i class="ti ti-arrow-left me-1"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        
        .verification-icon i {
            font-size: 2.5rem !important;
        }
        
        .fs-6 {
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
        
        .verification-icon i {
            font-size: 2rem !important;
        }
    }
</style>

@endsection
