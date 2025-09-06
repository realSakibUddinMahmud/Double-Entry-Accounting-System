<div class="dropdown mobile-user-menu">
    <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
    <div class="dropdown-menu dropdown-menu-right">
        @can('profile-view')
        <a class="dropdown-item" href="{{ route('profile.show') }}">My Profile</a>
        @endcan
        
        @can('profile-edit')
        <a class="dropdown-item" href="{{ route('profile.edit') }}">Settings</a>
        @endcan

        <a class="dropdown-item" href="{{ route('clear.all', ['id' => 'admin1234']) }}">Clear All</a>
        
        <a class="dropdown-item" href="{{ route('logout') }}"
           onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>