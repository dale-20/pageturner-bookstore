<header class="nxl-header">
    <div class="header-wrapper">
        <!-- Header Left -->
        <div class="header-left d-flex align-items-center gap-4">
            <!-- Mobile Toggler -->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>

            <!-- Navigation Toggle -->
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>

            <!-- Mega Menu -->
            {{--@include('layouts.partials.mega-menu')--}}
        </div>

        <!-- Header Right -->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                <!-- Search -->
                {{-- @include('layouts.partials.search') --}}

                <!-- Language -->
                {{-- @include('layouts.partials.language') --}}

                <!-- Full Screen -->
                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                            <i class="feather-maximize maximize"></i>
                            <i class="feather-minimize minimize"></i>
                        </a>
                    </div>
                </div>

                <!-- Theme Switcher -->
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>

                <!-- Timesheets -->
                {{--  @include('layouts.partials.timesheets') --}}

                <!-- Notifications -->
                {{-- @include('layouts.partials.notifications') --}}

                <!-- User Menu -->
                {{-- @include('layouts.partials.user-menu') --}}
            </div>
        </div>
    </div>
</header>