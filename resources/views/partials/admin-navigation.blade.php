<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('booksaw/images/page-turner.png') }}" alt="" class="logo logo-lg" />
                <img src="{{ asset('duralex/images/page-abbr.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
                
                <!-- Dashboards -->
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboards</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('dashboard') }}">CRM</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Analytics</a></li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-cast"></i></span>
                        <span class="nxl-mtext">Reports</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="">Sales Report</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Leads Report</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Project Report</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Timesheets Report</a></li>
                    </ul>
                </li>

                <!-- Applications -->
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-send"></i></span>
                        <span class="nxl-mtext">Applications</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="">Chat</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Email</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Tasks</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Notes</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Storage</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Calendar</a></li>
                    </ul>
                </li>

                <!-- Help Center -->
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-life-buoy"></i></span>
                        <span class="nxl-mtext">Help Center</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="#!">Support</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">KnowledgeBase</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="">Documentations</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</nav>