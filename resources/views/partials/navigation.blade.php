<div id="header-wrap">

    <div class="top-content">
        <div class="container-fluid">
            <div class="row">
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

            </div>
        </div>
    </div><!--top-right-->

    <header id="header">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-2">
                    <div class="main-logo">
                        <a href={{ route('home') }}><img src="{{ asset('booksaw/images/page_turner_logo.png') }}"
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
                                    <!-- <li class="menu-item"><a href="#popular-books" class="nav-link">Popular</a></li>
                                <li class="menu-item"><a href="#special-offer" class="nav-link">Offer</a></li>
                                <li class="menu-item"><a href="#latest-blog" class="nav-link">Articles</a></li>
                                <li class="menu-item"><a href="#download-app" class="nav-link">Download App</a></li> -->
                            </ul>

                            <div class="hamburger">
                                <span class="bar"></span>
                                <span class="bar"></span>
                                <span class="bar"></span>
                            </div>

                        </div>
                    </nav>

                </div>

            </div>
        </div>
    </header>

</div>