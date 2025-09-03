<div class="header">
    <div class="main-header">

        <!-- Logo -->
        <div class="header-left active">
            <a href="{{ route('home') }}" class="logo logo-normal">
                <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="Img">
            </a>
            <a href="{{ route('home') }}" class="logo logo-white">
                <img src="{{ asset('assets/img/ryofin-logo.png') }}" alt="Img">
            </a>
            <a href="{{ route('home') }}" class="logo-small">
                <img src="{{ asset('assets/img/logo-small.png') }}" alt="Img">
            </a>
        </div>
        <!-- /Logo -->

        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>

        <!-- Header Menu -->
        <ul class="nav user-menu">

            <!-- Search -->
            <li class="nav-item nav-searchinputs">
                <div class="top-nav-search">
                    <a href="javascript:void(0);" class="responsive-search">
                        <i class="fa fa-search"></i>
                    </a>
                    {{-- @include('layouts.search') --}}
                </div>
            </li>
            <!-- /Search -->

            <!-- Select Store -->
            {{-- @include('layouts.store-select') --}}
            <!-- /Select Store -->

            @include('layouts.add-new')
            
            {{-- <li class="nav-item pos-nav">
                <a href="pos.html" class="btn btn-dark btn-md d-inline-flex align-items-center">
                    <i class="ti ti-device-laptop me-1"></i>POS
                </a>
            </li> --}}

            <!-- Flag -->
            {{-- @include('layouts.language') --}}
            <!-- /Flag -->

            <li class="nav-item nav-item-box">
                <a href="javascript:void(0);" id="btnFullscreen">
                    <i class="ti ti-maximize"></i>
                </a>
            </li>
            {{-- <li class="nav-item nav-item-box">
                <a href="email.html">
                    <i class="ti ti-mail"></i>
                    <span class="badge rounded-pill">1</span>
                </a>
            </li> --}}
            <!-- Notifications -->
            {{-- @include('layouts.notification') --}}
            <!-- /Notifications -->

            {{-- <li class="nav-item nav-item-box">
                <a href="general-settings.html"><i class="ti ti-settings"></i></a>
            </li> --}}

            @include('layouts.user-profile')
        </ul>
        <!-- /Header Menu -->

        <!-- Mobile Menu -->
        @include('layouts.user-profile-mobile')
        <!-- /Mobile Menu -->
    </div>
</div>