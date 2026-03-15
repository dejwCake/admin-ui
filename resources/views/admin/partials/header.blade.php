<header class="header header-sticky">
    <button class="header-toggler d-lg-none me-2" type="button" data-coreui-toggle="sidebar">
        <i class="fa-solid fa-bars"></i>
    </button>
	@if(View::exists('admin.layout.logo'))
        @include('admin.layout.logo')
	@endif
    <ul class="header-nav ms-auto">
        <li class="nav-item dropdown">
            <a role="button" class="dropdown-toggle nav-link" data-coreui-toggle="dropdown" data-coreui-display="static">
                <span>
                    @if($adminUserAvatarUrl)
                        <img src="{{ $adminUserAvatarUrl }}" class="avatar-photo">
                    @elseif($adminUserInitials)
                        <span class="avatar-initials">{{ $adminUserInitials }}</span>
                    @else
                        <span class="avatar-initials"><i class="fa fa-user"></i></span>
                    @endif

                    <span class="hidden-md-down">{{ $adminUserFullName }}</span>
                </span>
                <span class="caret"></span>
            </a>
            @if(View::exists('admin.layout.profile-dropdown'))
                @include('admin.layout.profile-dropdown')
            @endif
        </li>
    </ul>
</header>
