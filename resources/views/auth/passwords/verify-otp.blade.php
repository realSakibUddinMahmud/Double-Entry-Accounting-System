@extends('layouts.app')
@section('title', 'Verify OTP')
@section('content')
<div class="login-wrapper login-new">
    <div class="row w-100">
        <div class="col-5 mx-auto">
            <div class="login-content user-login">
                <div class="login-logo">
                    <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="img">
                    <a href="{{ url('/') }}" class="login-logo logo-white">
                        <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Img">
                    </a>
                </div>
                <form method="POST" action="{{ route('password.verify.otp') }}" id="otpForm">
                    @csrf
                <div class="card">
                    <div class="card-body p-5">
                        <div class="login-userheading">
                            <h3>{{ __('Verify OTP') }}</h3>
                            <h4>Enter the 5-digit OTP sent to your phone.</h4>
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

                    
                            <input type="hidden" name="phone" id="hiddenPhone" value="{{ $phone ?? '' }}">
                            <input type="hidden" name="otp" id="combinedOtp">

                            <div class="mb-3">
                                <label class="form-label">{{ __('Enter OTP') }}</label>
                                <div class="d-flex justify-content-center gap-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <input type="text"
                                            class="form-control text-center @error('otp') is-invalid @enderror"
                                            name="otp{{ $i }}" id="otp{{ $i }}" maxlength="1"
                                            inputmode="numeric" pattern="\d*" required style="width:40px;">
                                    @endfor
                                </div>
                                @error('otp')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-login">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('Verify OTP') }}
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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('otpForm');
        const phoneInput = document.getElementById('hiddenPhone');
        const otpInputs = document.querySelectorAll('input[id^="otp"]');
        const combinedOtp = document.getElementById('combinedOtp');

        // Phone number validation
        if (!phoneInput.value) {
            alert('System error: Phone number missing. Please request a new OTP.');
            window.location.href = "{{ route('password.reset.request.form') }}";
            return;
        }

        // Auto-focus first OTP input
        if (otpInputs.length > 0) {
            otpInputs[0].focus();
        }

        // Handle OTP input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                updateCombinedOtp();

                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        function updateCombinedOtp() {
            combinedOtp.value = Array.from(otpInputs).map(input => input.value).join('');
        }

        form.addEventListener('submit', function(e) {
            updateCombinedOtp();

            if (combinedOtp.value.length !== 5) {
                // Let Laravel handle the validation
                return true;
            }
        });
    });
</script>
@endsection