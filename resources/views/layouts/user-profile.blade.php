<li class="nav-item dropdown has-arrow main-drop profile-nav">
    <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
        <span class="user-info p-0">
            <span class="user-letter">
                <img src="{{ asset('assets/img/profiles/avator.png') }}" alt="Img" class="img-fluid">
            </span>
        </span>
    </a>
    <div class="dropdown-menu menu-drop-user">
        <div class="profileset d-flex align-items-center">
            <span class="user-img me-2">
                <img src="{{ asset('assets/img/profiles/avator.png') }}" alt="Img">
            </span>
            <div>
                <h6 class="fw-medium">{{ Auth::user()->name }}</h6>
                {{-- <p>Admin</p> --}}
            </div>
        </div>
        
        @can('profile-view')
        <a class="dropdown-item" href="{{ route('profile.show') }}"><i class="ti ti-user-circle me-2"></i>MyProfile</a>
        @endcan
        
        @can('profile-edit')
        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti ti-settings-2 me-2"></i>Edit</a>
        @endcan

        @can('company-profile-show')
            <a class="dropdown-item" href="{{ route('company.profile') }}"><i class="ti ti-building me-2"></i>MyCompany</a>
        @endcan

        <a class="dropdown-item" href="{{ route('clear.all', ['id' => 'admin1234']) }}"><i class="ti ti-trash me-2"></i>Clear All</a>

        <hr class="my-2">
        
        @auth
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                <i class="ti ti-logout me-2"></i>{{ __('Logout') }}
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endauth
    </div>
</li>