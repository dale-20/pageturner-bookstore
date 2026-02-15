<div id="header-wrap">
    <div class="top-content">
        <div class="container-fluid">
            <div class="row">
                @auth
                    {{-- SHOW WHEN LOGGED IN --}}
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="right-element d-flex justify-content-end align-items-center">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                        style="width: 28px; height: 28px; font-size: 12px;">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    {{ auth()->user()->name }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                                            <i class="icon icon-dashboard me-2"></i>Dashboard
                                        </a>
                                    </li>
                                    @if(auth()->user()->isAdmin())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                                <i class="icon icon-settings me-2"></i>Admin Panel
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="icon icon-user me-2"></i>My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('orders.index') }}">
                                            <i class="icon icon-cart me-2"></i>My Orders
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="icon icon-logout me-2"></i>Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- SHOW WHEN NOT LOGGED IN --}}
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="right-element">
                            <a href="{{ route('login') }}"
                                class="btn border-2 border-gray-300 text-gray-700 px-6 py-2 rounded-md font-medium hover:border-accent hover:text-accent transition-colors">
                                Login
                            </a>
                            <a href="{{ route('register') }}"
                                class="btn btn-accent px-6 py-2 rounded-md font-medium hover:opacity-90 transition-opacity">
                                Register
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div><!--top-right-->

    <header id="header">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-2">
                    <div class="main-logo">
                        <a href={{ route('home') }}><img src="{{ asset('booksaw/images/page-turner.png') }}"
                                alt="logo"></a>
                    </div>

                </div>

                <div class="col-md-10">

                    <nav id="navbar">
                        <div class="main-menu stellarnav">
                            <ul class="menu-list">
                                <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                                    <a href="{{ route('home') }}">Home</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('books.index') ? 'active' : '' }}">
                                    <a href="{{ route('books.index') }}" class="nav-link">Browse</a>
                                </li>

                            </ul>
                        </div>
                    </nav>

                </div>

            </div>
        </div>
    </header>

</div>