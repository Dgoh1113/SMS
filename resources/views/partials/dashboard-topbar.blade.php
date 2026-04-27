<header class="dashboard-topbar" data-scroll-anchor>
    <button type="button" class="dashboard-topbar-toggle" id="topbarSidebarToggle" aria-label="Toggle navigation">
        <span class="dashboard-topbar-toggle-inner"></span>
    </button>
    <div class="dashboard-topbar-actions">
        <div class="dashboard-topbar-actions-cluster">
            <button type="button" class="dashboard-icon-btn top-right-btn dashboard-topbar-icon-btn dashboard-bookmark-btn" id="guideCatalogTrigger" title="Guide" aria-label="Guide" aria-expanded="false" aria-haspopup="dialog">
                <i class="bi bi-bookmark-fill dashboard-topbar-icon-symbol" aria-hidden="true"></i>
            </button>
            <button type="button" class="dashboard-icon-btn top-right-btn dashboard-topbar-icon-btn dashboard-theme-toggle" data-theme-toggle aria-label="Enable dark mode" title="Enable dark mode">
                <i class="bi bi-moon-fill dashboard-topbar-icon-symbol" data-theme-icon aria-hidden="true"></i>
            </button>
            @if (session('user_role') === 'dealer')
                <div class="dashboard-notifications">
                    <button type="button" class="dashboard-icon-btn top-right-btn dashboard-topbar-icon-btn dashboard-bell-btn" id="dealerNotificationsTrigger" aria-expanded="false" aria-haspopup="true" title="Notifications" aria-label="Notifications">
                        <i class="bi bi-bell-fill dashboard-topbar-icon-symbol" aria-hidden="true"></i>
                        <span class="dashboard-notifications-dot" id="dealerNotificationsDot" hidden></span>
                    </button>
                    <div class="dashboard-notifications-menu" id="dealerNotificationsMenu" hidden>
                        <div class="dashboard-notifications-header">
                            <span>Notifications</span>
                            <button type="button" class="dashboard-notifications-mark-read" id="dealerMarkAllReadBtn">Mark all as read</button>
                        </div>
                        <div class="dashboard-notifications-list" id="dealerNotificationsList">
                            <div class="dashboard-notifications-empty">Loading...</div>
                        </div>
                    </div>
                </div>
            @else
                <button type="button" class="dashboard-icon-btn top-right-btn dashboard-topbar-icon-btn dashboard-bell-btn" title="Notifications" aria-label="Notifications">
                    <i class="bi bi-bell-fill dashboard-topbar-icon-symbol" aria-hidden="true"></i>
                </button>
            @endif
            @php
                $avatarInitial = strtoupper(substr(session('user_email', 'U'), 0, 1));
                $avatarLetter = (ctype_alpha($avatarInitial) ? $avatarInitial : 'U');
            @endphp
            <div class="dashboard-profile-dropdown">
                <button type="button" class="dashboard-profile-btn dashboard-topbar-avatar-btn" id="profileDropdownTrigger" aria-expanded="false" aria-haspopup="true" title="{{ session('user_email', '') }}">
                    <div class="dashboard-user-avatar dashboard-avatar-{{ $avatarLetter }}">{{ $avatarInitial }}</div>
                </button>
                <div class="dashboard-profile-menu" id="profileDropdownMenu" hidden>
                    <div class="dashboard-profile-card">
                        <div class="dashboard-profile-avatar-lg dashboard-avatar-{{ $avatarLetter }}">{{ $avatarInitial }}</div>
                        <div class="dashboard-profile-email">{{ session('user_email', '') }}</div>
                        @if(session('user_alias'))
                            <div class="dashboard-profile-alias">{{ strtoupper(session('user_alias')) }}</div>
                        @endif
                        <div class="dashboard-profile-actions">
                            <button type="button" class="dashboard-profile-passkey-link" id="profileRegisterPasskeyBtn">
                                <span>Manage Passkey</span>
                            </button>
                            <form action="{{ route('logout') }}" method="POST" class="dashboard-profile-signout-form">
                                @csrf
                                <button type="submit" class="dashboard-profile-signout-btn">
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
