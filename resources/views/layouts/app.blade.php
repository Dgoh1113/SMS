<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SQL Sales Management System')</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <script>
        (function () {
            try {
                if (localStorage.getItem('sqlsms-theme') === 'dark') {
                    document.documentElement.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260420-05">
    <script src="{{ asset('js/passkey-registration.js') }}"></script>
    <script>
        // Apply sidebar state ASAP (prevents flicker on page navigation)
        (function () {
            try {
                var collapsed = localStorage.getItem('dashboard-sidebar-collapsed') === '1';
                if (collapsed) {
                    document.documentElement.classList.add('dashboard-root-sidebar-collapsed-preload');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body>
@php
    $isAdminGuideRole = in_array(session('user_role'), ['admin', 'manager'], true);

    $guideTopics = $isAdminGuideRole
        ? [
            [
                'id' => 'register-passkey',
                'icon' => 'bi-key-fill',
                'label' => 'Register passkey',
                'subtitle' => 'Security setup',
                'badge' => 'Security',
                'title' => 'Register passkey',
                'intro' => 'Use a passkey to sign in faster and more safely without typing your password every time.',
                'stepsHeading' => 'How to create',
                'steps' => [
                    'Click your profile icon in the top-right corner.',
                    'Choose Register passkey.',
                    'Select Use Phone / Scan QR or Register On This Device.',
                    'Follow your browser or device prompt to save the passkey.',
                    'Next time, use the passkey sign-in option when available.',
                ],
                'tipsHeading' => 'Helpful tips',
                'tips' => [
                    'Use your own trusted device or browser profile when saving a passkey.',
                    'If the prompt is cancelled, you can simply open the setup again and retry.',
                    'You can register more than one passkey later as a backup option.',
                ],
                'note' => 'If you are setting this up for the first time, the phone / QR option is usually the easiest path.',
                'primaryAction' => 'passkey',
                'primaryActionLabel' => 'Open passkey setup',
            ],
            [
                'id' => 'create-inquiry',
                'icon' => 'bi-plus-square-fill',
                'label' => 'Create inquiry',
                'subtitle' => 'Lead capture',
                'badge' => 'Daily work',
                'title' => 'Create a new inquiry',
                'intro' => 'Use the inquiry form to capture a lead cleanly so assignment, follow-up, and reporting stay accurate.',
                'stepsHeading' => 'How to create',
                'steps' => [
                    'Open Inquiries and click Add new inquiry.',
                    'Fill the required company, contact, location, and product details.',
                    'Check source, business nature, and any referral details before saving.',
                    'Save the inquiry and review the row once it appears in the table.',
                ],
                'tipsHeading' => 'What matters most',
                'tips' => [
                    'Accurate contact and location details make dealer assignment easier.',
                    'Keep product selections clear so reports and conversion tracking stay useful.',
                    'Use message notes when the lead has special requirements.',
                ],
                'note' => 'A clean inquiry record reduces rework later when dealers start following up.',
            ],
            [
                'id' => 'assign-dealer',
                'icon' => 'bi-person-check-fill',
                'label' => 'Assign dealer',
                'subtitle' => 'Routing work',
                'badge' => 'Daily work',
                'title' => 'Assign an inquiry to a dealer',
                'intro' => 'The assign flow helps you match an inquiry to the best dealer based on area, workload, and conversion history.',
                'stepsHeading' => 'How to assign',
                'steps' => [
                    'Open the inquiry row and click the assign action.',
                    'Review the Select dealer table in the modal.',
                    'Use filters like postcode, city, total lead, total closed, or conversion rate to narrow the list.',
                    'Click the dealer row you want, then confirm the assignment.',
                ],
                'tipsHeading' => 'Selection tips',
                'tips' => [
                    'Check location fields first so the dealer can respond faster.',
                    'Use conversion rate together with total lead, not by itself.',
                    'If two dealers look similar, review current workload before assigning.',
                ],
                'note' => 'The best assignment is usually the one that balances suitability and current capacity.',
            ],
            [
                'id' => 'update-status',
                'icon' => 'bi-pencil-square',
                'label' => 'Update inquiry status',
                'subtitle' => 'Progress tracking',
                'badge' => 'Daily work',
                'title' => 'Update inquiry progress and status',
                'intro' => 'Keep inquiry status current so your tables, dashboard, and reports reflect the real pipeline.',
                'stepsHeading' => 'How to update',
                'steps' => [
                    'Open the inquiry details or edit flow for the selected lead.',
                    'Review the latest activity, assignment, and notes.',
                    'Change the status or follow-up information based on the latest outcome.',
                    'Save the update so the new status appears in the table and reports.',
                ],
                'tipsHeading' => 'Keep it reliable',
                'tips' => [
                    'Update status as soon as a meaningful progress change happens.',
                    'Add notes when the next action or reason matters.',
                    'Avoid leaving completed or failed cases in older statuses.',
                ],
                'note' => 'Timely status updates make the monthly performance cards much more trustworthy.',
            ],
            [
                'id' => 'table-tools',
                'icon' => 'bi-funnel-fill',
                'label' => 'Use table tools',
                'subtitle' => 'Filters and sorting',
                'badge' => 'Productivity',
                'title' => 'Use filters, sort, and columns',
                'intro' => 'Most admin tables support quick searching, clickable sorting, and column visibility so you can focus on the right records faster.',
                'stepsHeading' => 'How to use',
                'steps' => [
                    'Type into the header filter boxes to narrow the visible rows.',
                    'Click a sortable header to switch between ascending and descending order.',
                    'Open Columns to hide or show fields depending on what you need to review.',
                    'Use Clear filters to reset the view back to the full list.',
                ],
                'tipsHeading' => 'Best practice',
                'tips' => [
                    'Use fewer visible columns on smaller screens to keep the table readable.',
                    'For numeric fields, use the operator filter when available.',
                    'Reset filters before assuming data is missing.',
                ],
                'note' => 'The quickest reviews usually come from combining one or two filters instead of scanning the whole table.',
            ],
            [
                'id' => 'reports-overview',
                'icon' => 'bi-bar-chart-fill',
                'label' => 'Read reports',
                'subtitle' => 'Monthly performance',
                'badge' => 'Reports',
                'title' => 'Read the admin reports',
                'intro' => 'Admin reports combine inquiry creation, latest activity movement, and conversion results to help you judge pipeline quality.',
                'stepsHeading' => 'What to check first',
                'steps' => [
                    'Choose the month, year, and scope at the top of the report.',
                    'Review the top status cards for the latest monthly movement.',
                    'Use Inquiry Trends to see newly created inquiries in that month.',
                    'Use Status Report and Product Conversion to understand activity outcomes and closed-case results.',
                ],
                'tipsHeading' => 'Reading guidance',
                'tips' => [
                    'Inquiry Trends shows new inquiries created in the selected month.',
                    'Status cards reflect latest status activity in that month, which can include older leads.',
                    'Compare cards and charts together before drawing conclusions.',
                ],
                'note' => 'If totals feel different between sections, it is usually because creation-date data and activity-date data are measuring different things.',
            ],
            [
                'id' => 'maintain-users',
                'icon' => 'bi-people-fill',
                'label' => 'Maintain users',
                'subtitle' => 'Admin setup',
                'badge' => 'Admin',
                'title' => 'Maintain users and access',
                'intro' => 'Use Maintain Users to review user access, passkey status, activity state, and account details in one place.',
                'stepsHeading' => 'How to manage',
                'steps' => [
                    'Open Maintain Users from the sidebar.',
                    'Use filters to find a user by email, alias, role, company, or passkey status.',
                    'Review Active and Last Login before making changes.',
                    'Use Add User or the edit action when you need to create or update an account.',
                ],
                'tipsHeading' => 'Admin checks',
                'tips' => [
                    'A Ready to send passkey state usually means setup is still pending.',
                    'Last Login helps confirm whether a user is actually using the account.',
                    'Keep aliases and company names consistent for easier searching later.',
                ],
                'note' => 'A tidy user list makes passkey rollout and support much easier.',
            ],
        ]
        : [
            [
                'id' => 'register-passkey',
                'icon' => 'bi-key-fill',
                'label' => 'Register passkey',
                'subtitle' => 'Security setup',
                'badge' => 'Security',
                'title' => 'Register passkey',
                'intro' => 'Use a passkey so you can sign in faster and more safely from your trusted device.',
                'stepsHeading' => 'How to create',
                'steps' => [
                    'Click your profile icon in the top-right corner.',
                    'Choose Register passkey.',
                    'Select Use Phone / Scan QR or Register On This Device.',
                    'Follow your browser or device prompt to save the passkey.',
                    'Use the passkey option the next time you sign in.',
                ],
                'tipsHeading' => 'Helpful tips',
                'tips' => [
                    'The phone / QR path is often easiest for first-time setup.',
                    'You can retry immediately if the browser prompt is cancelled.',
                    'Saving a backup passkey on another trusted device is a good safety step.',
                ],
                'note' => 'A passkey helps you sign in quickly without depending on a typed password every time.',
                'primaryAction' => 'passkey',
                'primaryActionLabel' => 'Open passkey setup',
            ],
            [
                'id' => 'my-inquiries',
                'icon' => 'bi-journal-text',
                'label' => 'My inquiries',
                'subtitle' => 'Daily queue',
                'badge' => 'Daily work',
                'title' => 'Work with My Inquiries',
                'intro' => 'My Inquiries is your main working list for leads that need review, progress updates, or action.',
                'stepsHeading' => 'How to use',
                'steps' => [
                    'Open My Inquiries from the dealer sidebar or dashboard links.',
                    'Use the tabs to switch between different inquiry groups.',
                    'Filter, sort, or adjust columns so the current queue is easier to review.',
                    'Open a row to view details or update the inquiry when needed.',
                ],
                'tipsHeading' => 'Daily habit',
                'tips' => [
                    'Start with the oldest or most urgent open leads first.',
                    'Use filters when you only want one city, status, or product group.',
                    'Keep inquiry progress up to date before checking payouts or reports.',
                ],
                'note' => 'This page works best as your main daily checklist for follow-up work.',
            ],
            [
                'id' => 'update-progress',
                'icon' => 'bi-arrow-repeat',
                'label' => 'Update progress',
                'subtitle' => 'Status tracking',
                'badge' => 'Daily work',
                'title' => 'Update inquiry progress',
                'intro' => 'Every real progress change should be reflected quickly so your dashboard, reports, and payouts stay accurate.',
                'stepsHeading' => 'How to update',
                'steps' => [
                    'Open the inquiry you are working on.',
                    'Review the latest details, notes, and customer response.',
                    'Move the inquiry to the correct next status or outcome.',
                    'Save the update so the latest activity is recorded.',
                ],
                'tipsHeading' => 'Keep it useful',
                'tips' => [
                    'Update after calls, demos, confirmations, or failed outcomes.',
                    'Add clear notes when a follow-up date or blocker matters.',
                    'Try not to leave inquiries parked in an older status after the outcome changes.',
                ],
                'note' => 'Consistent updates make both your dashboard cards and admin reports more meaningful.',
            ],
            [
                'id' => 'pending-payouts',
                'icon' => 'bi-cash-stack',
                'label' => 'Pending payouts',
                'subtitle' => 'Dealer earnings',
                'badge' => 'Daily work',
                'title' => 'Review Pending Payouts',
                'intro' => 'Pending Payouts helps you track completed cases that are waiting in the payout flow.',
                'stepsHeading' => 'How to review',
                'steps' => [
                    'Open Pending Payouts from the dealer inquiries area.',
                    'Use filters and sortable columns to check the specific completed cases you need.',
                    'Review completion details before raising any payout question.',
                    'Clear filters to return to the full completed list when done.',
                ],
                'tipsHeading' => 'Useful checks',
                'tips' => [
                    'Customer name, completion date, and referral code are often the fastest filters.',
                    'Use the Columns menu on smaller screens to focus on the fields you need.',
                    'Make sure inquiry progress is updated correctly first, because payouts depend on that history.',
                ],
                'note' => 'Pending payouts reflect completed work, so status accuracy earlier in the process is important.',
            ],
            [
                'id' => 'table-tools',
                'icon' => 'bi-funnel-fill',
                'label' => 'Use table tools',
                'subtitle' => 'Filters and sorting',
                'badge' => 'Productivity',
                'title' => 'Use filters, sort, and columns',
                'intro' => 'Dealer tables are built to help you narrow large lists quickly without leaving the page.',
                'stepsHeading' => 'How to use',
                'steps' => [
                    'Type in a header filter box to reduce the visible rows.',
                    'Click sortable headers to switch between ascending and descending order.',
                    'Open Columns to hide fields that are not useful for the current task.',
                    'Use Clear filters when you want to return to the full list.',
                ],
                'tipsHeading' => 'Best practice',
                'tips' => [
                    'On mobile, fewer columns usually gives a cleaner working view.',
                    'Use date and status filters together when checking recent progress.',
                    'If a list feels empty, reset filters before assuming data is missing.',
                ],
                'note' => 'A small amount of filtering usually saves a lot of scrolling.',
            ],
            [
                'id' => 'dealer-reports',
                'icon' => 'bi-graph-up-arrow',
                'label' => 'Read reports',
                'subtitle' => 'Performance view',
                'badge' => 'Reports',
                'title' => 'Read the dealer reports',
                'intro' => 'Dealer reports help you understand your inquiry flow, status mix, and product conversion performance over time.',
                'stepsHeading' => 'What to check first',
                'steps' => [
                    'Choose the month, year, or period filter at the top of the report.',
                    'Review the top cards to see your current status mix.',
                    'Use Inquiry Trends to see where activity is building or slowing down.',
                    'Compare Status Report and Product Conversion to understand what is turning into completed work.',
                ],
                'tipsHeading' => 'Reading guidance',
                'tips' => [
                    'Use the current month view when you want daily detail.',
                    'High conversion is strongest when the closed count is also meaningful.',
                    'Check dashboard and inquiry updates first if report numbers look unexpected.',
                ],
                'note' => 'Reports are most useful when inquiry status is kept current during daily work.',
            ],
            [
                'id' => 'dealer-dashboard',
                'icon' => 'bi-speedometer2',
                'label' => 'Use dashboard',
                'subtitle' => 'Quick monitoring',
                'badge' => 'Dashboard',
                'title' => 'Use the dealer dashboard',
                'intro' => 'Your dashboard is the fastest way to spot active inquiry movement, closed cases, and urgent follow-ups at a glance.',
                'stepsHeading' => 'What to watch',
                'steps' => [
                    'Start with Active Inquiries to see recent movement.',
                    'Check Closed Case to understand recent outcome trends.',
                    'Review High Priority Follow-ups so urgent work is not missed.',
                    'Jump into My Inquiries when something needs action.',
                ],
                'tipsHeading' => 'Good routine',
                'tips' => [
                    'Use the dashboard as a quick scan, then do the real work inside your tables.',
                    'If the charts look quiet, confirm that inquiry updates were saved correctly.',
                    'On mobile, scroll section by section instead of trying to read everything at once.',
                ],
                'note' => 'The dashboard is best used as a quick monitor, not a replacement for updating inquiries.',
            ],
        ];

    if (session('user_role') === 'manager') {
        $guideTopics = array_values(array_filter($guideTopics, function ($topic) {
            return ($topic['id'] ?? '') !== 'maintain-users';
        }));
    }

    $firstGuideTopic = $guideTopics[0];
@endphp

<div class="dashboard-root" id="dashboardRoot">
    @if (in_array(session('user_role'), ['admin', 'manager'], true))
        @include('partials.sidebar-admin')
    @elseif (session('user_role') === 'dealer')
        @include('partials.sidebar-dealer')
    @endif
  

    <main class="dashboard-main">
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
                                        <span>Register passkey</span>
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

        @if (session('error'))
            <div class="login-message login-error" style="margin:16px;" data-flash-message="1">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin:16px;" data-flash-message="1">{{ session('success') }}</div>
        @endif

        <div class="dashboard-passkey-quick-modal" id="profilePasskeyQuickModal" hidden>
            <div class="dashboard-passkey-quick-card" role="dialog" aria-modal="true" aria-labelledby="profilePasskeyQuickTitle">
                <button type="button" class="dashboard-passkey-quick-close" id="profilePasskeyQuickClose" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <h3 class="dashboard-passkey-quick-title" id="profilePasskeyQuickTitle">Register passkey</h3>
                <p class="dashboard-passkey-quick-subtitle">Choose where to save your new passkey.</p>
                <div class="dashboard-passkey-quick-actions">
                    <button type="button" class="login-primary-btn" id="profilePasskeyPhoneBtn" style="margin-top: 0;">
                        <i class="bi bi-phone" aria-hidden="true"></i>
                        <span>Use Phone / Scan QR</span>
                    </button>
                    <button type="button" class="login-passkey-btn" id="profilePasskeyDeviceBtn">
                        <i class="bi bi-laptop" aria-hidden="true"></i>
                        <span>Register On This Device</span>
                    </button>
                </div>
                <div class="dashboard-passkey-quick-status" id="profilePasskeyQuickStatus" hidden></div>
            </div>
        </div>

        <div class="dashboard-guide-modal" id="guideCatalogModal" hidden>
            <div class="dashboard-guide-card" role="dialog" aria-modal="true" aria-labelledby="guideCatalogTitle">
                <button type="button" class="dashboard-guide-close" id="guideCatalogClose" aria-label="Close guide">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <div class="dashboard-guide-layout">
                    <aside class="dashboard-guide-nav">
                        <span class="dashboard-guide-nav-label">Guide</span>
                        <h3 class="dashboard-guide-nav-title">Topic catalogue</h3>
                        <div class="dashboard-guide-topic-list">
                            @foreach ($guideTopics as $index => $topic)
                                <button
                                    type="button"
                                    class="dashboard-guide-topic-btn{{ $index === 0 ? ' is-active' : '' }}"
                                    data-guide-topic-index="{{ $index }}"
                                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                >
                                    <span class="dashboard-guide-topic-icon"><i class="bi {{ $topic['icon'] }}" aria-hidden="true"></i></span>
                                    <span class="dashboard-guide-topic-copy">
                                        <strong>{{ $topic['label'] }}</strong>
                                        <small>{{ $topic['subtitle'] }}</small>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </aside>
                    <section class="dashboard-guide-content">
                        <div class="dashboard-guide-header">
                            <span class="dashboard-guide-hero-icon" id="guideCatalogHeroIcon">
                                <i class="bi {{ $firstGuideTopic['icon'] }}" aria-hidden="true"></i>
                            </span>
                            <div class="dashboard-guide-header-copy">
                                <span class="dashboard-guide-badge" id="guideCatalogBadge">{{ $firstGuideTopic['badge'] }}</span>
                                <h2 class="dashboard-guide-title" id="guideCatalogTitle">{{ $firstGuideTopic['title'] }}</h2>
                                <p class="dashboard-guide-intro" id="guideCatalogIntro">{{ $firstGuideTopic['intro'] }}</p>
                            </div>
                        </div>

                        <div class="dashboard-guide-section">
                            <h4 id="guideCatalogStepsHeading">{{ $firstGuideTopic['stepsHeading'] }}</h4>
                            <ol class="dashboard-guide-steps" id="guideCatalogSteps">
                                @foreach ($firstGuideTopic['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>

                        <div class="dashboard-guide-section">
                            <h4 id="guideCatalogTipsHeading">{{ $firstGuideTopic['tipsHeading'] }}</h4>
                            <ul class="dashboard-guide-tips" id="guideCatalogTips">
                                @foreach ($firstGuideTopic['tips'] as $tip)
                                    <li>{{ $tip }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="dashboard-guide-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span id="guideCatalogNote">{{ $firstGuideTopic['note'] }}</span>
                        </div>

                        <div class="dashboard-guide-actions">
                            <button type="button" class="dashboard-guide-primary-btn" id="guideOpenPasskeyBtn"{{ ($firstGuideTopic['primaryAction'] ?? null) === 'passkey' ? '' : ' hidden' }}>
                                {{ $firstGuideTopic['primaryActionLabel'] ?? 'Open passkey setup' }}
                            </button>
                            <button type="button" class="dashboard-guide-secondary-btn" id="guideCloseActionBtn">Close</button>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="dashboard-main-body">
            @yield('content')
        </div>

        {{-- FIXED: Footer properly moved here inside the main tag --}}
        <footer class="dashboard-bottombar" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #6b7280;">
            <div class="dashboard-bottombar-left">
                <span class="dashboard-footer-text-main">&copy; {{ date('Y') }} E Stream Software. All rights reserved.</span>
            </div>
            <div class="dashboard-bottombar-right">
                <span>Designed &amp; Developed by <strong>Damien, WeiJian &amp; WenJun with &#x1F49C;</strong></span>
            </div>
        </footer>
    </main>
</div>

<script>
(function () {
    var THEME_KEY = 'sqlsms-theme';

    function getStoredTheme() {
        try {
            return localStorage.getItem(THEME_KEY) === 'dark' ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    function isDarkTheme() {
        return document.documentElement.classList.contains('theme-dark');
    }

    function updateThemeToggle(button) {
        if (!button) return;

        var dark = isDarkTheme();
        var icon = button.querySelector('[data-theme-icon]');
        button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('title', dark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('data-theme-state', dark ? 'dark' : 'light');

        if (icon) {
            icon.classList.remove('bi-moon-fill', 'bi-brightness-high-fill');
            icon.classList.add(dark ? 'bi-brightness-high-fill' : 'bi-moon-fill');
        }
    }

    function syncThemeToggles() {
        document.querySelectorAll('[data-theme-toggle]').forEach(updateThemeToggle);
    }

    var themeAnimationTimer = null;
    var themeToggleSpinTimer = null;

    function shouldAnimateTheme() {
        return !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    function runThemeAnimation() {
        if (!shouldAnimateTheme()) {
            return;
        }

        window.clearTimeout(themeAnimationTimer);
        document.documentElement.classList.add('theme-animating');
        themeAnimationTimer = window.setTimeout(function () {
            document.documentElement.classList.remove('theme-animating');
        }, 460);
    }

    function runThemeToggleSpin(sourceButton) {
        if (!shouldAnimateTheme() || !sourceButton) {
            return;
        }

        window.clearTimeout(themeToggleSpinTimer);
        document.querySelectorAll('[data-theme-toggle].is-spinning').forEach(function (button) {
            button.classList.remove('is-spinning');
        });
        sourceButton.classList.add('is-spinning');
        themeToggleSpinTimer = window.setTimeout(function () {
            sourceButton.classList.remove('is-spinning');
        }, 640);
    }

    function primeThemeOrigin(sourceButton) {
        if (!sourceButton || !sourceButton.getBoundingClientRect) {
            return;
        }

        var rect = sourceButton.getBoundingClientRect();
        document.documentElement.style.setProperty('--theme-origin-x', Math.round(rect.left + (rect.width / 2)) + 'px');
        document.documentElement.style.setProperty('--theme-origin-y', Math.round(rect.top + (rect.height / 2)) + 'px');
    }

    function commitTheme(theme) {
        var dark = theme === 'dark';
        document.documentElement.classList.toggle('theme-dark', dark);
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        syncThemeToggles();
    }

    function applyTheme(theme, options) {
        options = options || {};
        

        if (options.animate && shouldAnimateTheme() && typeof document.startViewTransition === 'function') {
            document.startViewTransition(function () {
                commitTheme(theme);
            });
            return;
        }

        if (options.animate) {
            runThemeAnimation();
        }

        commitTheme(theme);
    }

    function toggleTheme(event) {
        var nextTheme = isDarkTheme() ? 'light' : 'dark';
        var sourceButton = event && event.currentTarget ? event.currentTarget : null;
        primeThemeOrigin(sourceButton);
        runThemeToggleSpin(sourceButton);
        try {
            localStorage.setItem(THEME_KEY, nextTheme);
        } catch (e) {}
        applyTheme(nextTheme, { animate: true });
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        if (button.dataset.themeBound === '1') {
            updateThemeToggle(button);
            return;
        }

        button.dataset.themeBound = '1';
        updateThemeToggle(button);
        button.addEventListener('click', toggleTheme);
    });

    window.addEventListener('storage', function (event) {
        if (event.key === THEME_KEY) {
            applyTheme(getStoredTheme(), { animate: true });
        }
    });

    applyTheme(getStoredTheme());
})();
</script>

@push('scripts')
<script>
(function() {
    // Auto-hide flash messages (success/error) after 3s
    document.querySelectorAll('[data-flash-message="1"]').forEach(function(el) {
        setTimeout(function() {
            if (!el) return;
            el.style.transition = 'opacity 200ms ease';
            el.style.opacity = '0';
            setTimeout(function() { if (el && el.parentNode) el.parentNode.removeChild(el); }, 250);
        }, 3000);
    });

    var guideTrigger = document.getElementById('guideCatalogTrigger');
    var guideModal = document.getElementById('guideCatalogModal');
    var guideCloseBtn = document.getElementById('guideCatalogClose');
    var guideCloseActionBtn = document.getElementById('guideCloseActionBtn');
    var guideOpenPasskeyBtn = document.getElementById('guideOpenPasskeyBtn');
    var guideBadge = document.getElementById('guideCatalogBadge');
    var guideHeroIcon = document.getElementById('guideCatalogHeroIcon');
    var guideTitle = document.getElementById('guideCatalogTitle');
    var guideIntro = document.getElementById('guideCatalogIntro');
    var guideStepsHeading = document.getElementById('guideCatalogStepsHeading');
    var guideSteps = document.getElementById('guideCatalogSteps');
    var guideTipsHeading = document.getElementById('guideCatalogTipsHeading');
    var guideTips = document.getElementById('guideCatalogTips');
    var guideNote = document.getElementById('guideCatalogNote');
    var guideTopicButtons = Array.prototype.slice.call(document.querySelectorAll('[data-guide-topic-index]'));
    var guideTopics = @json($guideTopics);
    var activeGuideTopicIndex = 0;
    var trigger = document.getElementById('profileDropdownTrigger');
    var menu = document.getElementById('profileDropdownMenu');
    var passkeyTrigger = document.getElementById('profileRegisterPasskeyBtn');
    var passkeyModal = document.getElementById('profilePasskeyQuickModal');
    var passkeyCloseBtn = document.getElementById('profilePasskeyQuickClose');
    var passkeyDeviceBtn = document.getElementById('profilePasskeyDeviceBtn');
    var passkeyPhoneBtn = document.getElementById('profilePasskeyPhoneBtn');
    var passkeyStatus = document.getElementById('profilePasskeyQuickStatus');
    var passkeyUtils = window.SQLSMSPasskey;

    function hideProfileMenu() {
        if (!menu || !trigger) return;
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    }

    function syncDashboardOverlayLock() {
        var hasGuide = guideModal && !guideModal.hidden;
        var hasPasskey = passkeyModal && !passkeyModal.hidden;
        document.body.classList.toggle('dashboard-passkey-modal-open', !!(hasGuide || hasPasskey));
    }

    function escapeGuideHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderGuideList(items, ordered) {
        return (items || []).map(function(item) {
            return '<li>' + escapeGuideHtml(item) + '</li>';
        }).join('');
    }

    function renderGuideTopic(index) {
        if (!guideTopics || !guideTopics.length) {
            return;
        }

        var safeIndex = index >= 0 && index < guideTopics.length ? index : 0;
        var topic = guideTopics[safeIndex];
        activeGuideTopicIndex = safeIndex;

        if (guideHeroIcon) {
            guideHeroIcon.innerHTML = '<i class="bi ' + escapeGuideHtml(topic.icon || 'bi-bookmark-fill') + '" aria-hidden="true"></i>';
        }
        if (guideBadge) guideBadge.textContent = topic.badge || '';
        if (guideTitle) guideTitle.textContent = topic.title || '';
        if (guideIntro) guideIntro.textContent = topic.intro || '';
        if (guideStepsHeading) guideStepsHeading.textContent = topic.stepsHeading || 'Steps';
        if (guideSteps) guideSteps.innerHTML = renderGuideList(topic.steps || [], true);
        if (guideTipsHeading) guideTipsHeading.textContent = topic.tipsHeading || 'Helpful tips';
        if (guideTips) guideTips.innerHTML = renderGuideList(topic.tips || [], false);
        if (guideNote) guideNote.textContent = topic.note || '';

        if (guideOpenPasskeyBtn) {
            var isPasskeyTopic = topic.primaryAction === 'passkey';
            guideOpenPasskeyBtn.hidden = !isPasskeyTopic;
            guideOpenPasskeyBtn.textContent = topic.primaryActionLabel || 'Open passkey setup';
        }

        guideTopicButtons.forEach(function(button) {
            var buttonIndex = Number(button.getAttribute('data-guide-topic-index'));
            var isActive = buttonIndex === safeIndex;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    }

    function setGuideModalOpen(open) {
        if (!guideModal || !guideTrigger) return;
        guideModal.hidden = !open;
        guideTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            renderGuideTopic(activeGuideTopicIndex);
        }
        syncDashboardOverlayLock();
    }

    function showFlashMessage(type, message) {
        var mainBody = document.querySelector('.dashboard-main-body');
        var host = document.querySelector('.dashboard-main');
        if (!mainBody || !host) {
            alert(message);
            return;
        }

        var flash = document.createElement('div');
        flash.className = 'login-message ' + (type === 'error' ? 'login-error' : 'login-success');
        flash.style.margin = '16px';
        flash.textContent = message;
        host.insertBefore(flash, mainBody);

        setTimeout(function() {
            flash.style.transition = 'opacity 200ms ease';
            flash.style.opacity = '0';
            setTimeout(function() {
                if (flash.parentNode) {
                    flash.parentNode.removeChild(flash);
                }
            }, 250);
        }, 3000);
    }

    function enforceFourDigitYearOnDateInput(field) {
        if (!field || field.type !== 'date') {
            return;
        }

        if (!field.hasAttribute('min')) {
            field.setAttribute('min', '1000-01-01');
        }

        if (!field.hasAttribute('max')) {
            field.setAttribute('max', '9999-12-31');
        }

        if (!field.value) {
            return;
        }

        var segments = String(field.value).split('-');
        if (segments.length !== 3) {
            return;
        }

        var year = String(segments[0] || '');
        if (year.length <= 4) {
            return;
        }

        field.value = year.slice(0, 4) + '-' + String(segments[1] || '') + '-' + String(segments[2] || '');
    }

    function initSharedDateInputGuards(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('input[type="date"]').forEach(function(field) {
            enforceFourDigitYearOnDateInput(field);
        });
    }

    initSharedDateInputGuards(document);

    ['focusin', 'input', 'change', 'blur'].forEach(function(eventName) {
        document.addEventListener(eventName, function(e) {
            var target = e.target;
            if (target && target.matches && target.matches('input[type="date"]')) {
                enforceFourDigitYearOnDateInput(target);
            }
        }, true);
    });

    function setPasskeyModalOpen(open) {
        if (!passkeyModal) return;
        passkeyModal.hidden = !open;
        syncDashboardOverlayLock();
        if (!open) {
            setPasskeyStatus('', '');
            setPasskeyButtonsDisabled(false);
        }
    }

    function setPasskeyButtonsDisabled(disabled) {
        [passkeyDeviceBtn, passkeyPhoneBtn].forEach(function(button) {
            if (button) button.disabled = !!disabled;
        });
    }

    function setPasskeyStatus(type, message) {
        if (!passkeyStatus) return;
        passkeyStatus.hidden = !message;
        passkeyStatus.className = 'dashboard-passkey-quick-status';
        if (!message) {
            passkeyStatus.textContent = '';
            return;
        }
        passkeyStatus.classList.add('login-message', type === 'error' ? 'login-error' : 'login-success');
        passkeyStatus.textContent = message;
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function passkeyErrorMessage(err) {
        if (err && err.cancelled) {
            return '';
        }
        if (err && err.name === 'NotAllowedError') {
            return 'Passkey registration was cancelled.';
        }
        if (err && err.message) {
            return err.message;
        }
        return 'Passkey registration failed.';
    }

    function startProfilePasskeyRegistration(preference) {
        if (!window.PublicKeyCredential || !passkeyUtils) {
            setPasskeyStatus('error', 'Passkeys are not supported in this browser.');
            return;
        }

        setPasskeyStatus('', '');
        setPasskeyButtonsDisabled(true);

        var nickname = preference === 'phone' ? 'Phone passkey' : 'This device';

        passkeyUtils.register({
            preference: preference,
            optionsUrl: '{{ route("passkey.register.options") }}',
            verifyUrl: '{{ route("passkey.register.verify") }}',
            csrfToken: getCsrfToken(),
            getNickname: function () {
                return nickname;
            }
        })
        .then(function(result) {
            setPasskeyModalOpen(false);

            if (result && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            showFlashMessage('success', 'Passkey registered successfully.');
        })
        .catch(function(err) {
            setPasskeyButtonsDisabled(false);
            var message = passkeyErrorMessage(err);
            if (message) {
                setPasskeyStatus('error', message);
            }
        });
    }

    if (trigger && menu) {
        function toggle() {
            var open = !menu.hidden;
            menu.hidden = open;
            trigger.setAttribute('aria-expanded', !open);
        }
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggle();
        });
        document.addEventListener('click', function() {
            hideProfileMenu();
        });
        menu.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    if (passkeyTrigger) {
        passkeyTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideProfileMenu();
            setPasskeyModalOpen(true);
        });
    }

    if (guideTrigger) {
        guideTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideProfileMenu();
            setGuideModalOpen(true);
        });
    }

    guideTopicButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            renderGuideTopic(Number(button.getAttribute('data-guide-topic-index')));
        });
    });

    if (guideCloseBtn) {
        guideCloseBtn.addEventListener('click', function() {
            setGuideModalOpen(false);
        });
    }

    if (guideCloseActionBtn) {
        guideCloseActionBtn.addEventListener('click', function() {
            setGuideModalOpen(false);
        });
    }

    if (guideModal) {
        guideModal.addEventListener('click', function(e) {
            if (e.target === guideModal) {
                setGuideModalOpen(false);
            }
        });
    }

    if (guideOpenPasskeyBtn) {
        guideOpenPasskeyBtn.addEventListener('click', function() {
            setGuideModalOpen(false);
            setPasskeyModalOpen(true);
        });
    }

    if (passkeyCloseBtn) {
        passkeyCloseBtn.addEventListener('click', function() {
            setPasskeyModalOpen(false);
        });
    }

    if (passkeyModal) {
        passkeyModal.addEventListener('click', function(e) {
            if (e.target === passkeyModal) {
                setPasskeyModalOpen(false);
            }
        });
    }

    if (passkeyDeviceBtn) {
        passkeyDeviceBtn.addEventListener('click', function() {
            startProfilePasskeyRegistration('device');
        });
    }

    if (passkeyPhoneBtn) {
        passkeyPhoneBtn.addEventListener('click', function() {
            startProfilePasskeyRegistration('phone');
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && guideModal && !guideModal.hidden) {
            setGuideModalOpen(false);
        }
        if (e.key === 'Escape' && passkeyModal && !passkeyModal.hidden) {
            setPasskeyModalOpen(false);
        }
    });

    // Dealer notifications dropdown (bell) - LocalStorage Version
    var nTrigger = document.getElementById('dealerNotificationsTrigger');
    var nMenu = document.getElementById('dealerNotificationsMenu');
    var nList = document.getElementById('dealerNotificationsList');
    var nDot = document.getElementById('dealerNotificationsDot');
    var markAllBtn = document.getElementById('dealerMarkAllReadBtn');
    var currentNotifIds = []; // Stores IDs of currently loaded notifications
    var nLoaded = false;
    
    // Key used to save data in the browser
    var SEEN_KEY = 'dealer.notifications.seen.v1';
    
    function getSeen() {
        try { return JSON.parse(localStorage.getItem(SEEN_KEY) || '{}') || {}; } catch (e) { return {}; }
    }
    function setSeen(obj) {
        try { localStorage.setItem(SEEN_KEY, JSON.stringify(obj || {})); } catch (e) {}
    }
    
    function renderNotifications(items) {
        if (!nList) return;
        currentNotifIds = []; // Clear the array on new render

        if (!items || !items.length) {
            nList.innerHTML = '<div class="dashboard-notifications-empty">No notifications</div>';
            if (nDot) nDot.hidden = true;
            return;
        }
        
        var seen = getSeen();
        var hasUnseen = false;
        nList.innerHTML = '';
        
        // Limit to exactly 20 items
        var displayItems = items.slice(0, 20);
        
        displayItems.forEach(function(it) {
            // It is critical that your server provides a unique 'id' for each notification
            var id = String(it.id || it.lead_id || ''); 
            var leadId = String(it.lead_id || '');
            var title = (it.title || ('Inquiry #SQL-' + leadId)).toString();
            var desc = (it.description || '').toString();
            var time = (it.time || '').toString();
            var targetUrl = (it.target_url || ('/dealer/inquiries?lead=' + encodeURIComponent(leadId) + '&fromNotif=' + encodeURIComponent(id))).toString();
            
            // Check if this ID exists in our local browser storage
            var isSeen = !!seen[id];
            
            if (!isSeen) {
                hasUnseen = true;
                currentNotifIds.push(id); // Save ID so 'Mark all' knows what to update
            }

            var a = document.createElement('a');
            a.href = targetUrl;
            a.className = 'dashboard-notification-item' + (isSeen ? '' : ' dashboard-notification-item--new');
            a.setAttribute('data-notif-id', id);
            a.innerHTML =
                '<div class="dashboard-notification-title">' + title.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' +
                (desc ? '<div class="dashboard-notification-desc">' + desc.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '') +
                (time ? '<div class="dashboard-notification-time">' + time.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '');
            
            // Mark individual item as read when clicked
            a.addEventListener('click', function(e) {
                e.preventDefault();
                var s = getSeen();
                s[id] = Date.now();
                setSeen(s);
                window.location.href = targetUrl;
            });
            nList.appendChild(a);
        });
        
        if (nDot) nDot.hidden = !hasUnseen;
    }

    function loadNotifications() {
        if (!nList) return;
        fetch('/dealer/notifications', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) { renderNotifications((data && data.items) ? data.items : []); })
            .catch(function() { 
                // Only show error text if the menu is actively open and it's the first manual load
                if (!nMenu.hidden && !nLoaded) {
                    nList.innerHTML = '<div class="dashboard-notifications-empty">Failed to load</div>'; 
                }
            });
    }

    // Mark all as read functionality
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent dropdown from closing
            var s = getSeen();
            
            // Add all unread IDs to the local storage 'seen' object
            currentNotifIds.forEach(function(id) {
                s[id] = Date.now();
            });
            setSeen(s);
            currentNotifIds = []; // Clear array
            
            // Update UI instantly to remove the blue background and dot
            document.querySelectorAll('.dashboard-notification-item--new').forEach(function(el) {
                el.classList.remove('dashboard-notification-item--new');
            });
            if (nDot) nDot.hidden = true;
        });
    }

    if (nTrigger && nMenu) {
        nTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var open = !nMenu.hidden;
            nMenu.hidden = open;
            nTrigger.setAttribute('aria-expanded', String(!open));
            if (!open && !nLoaded) {
                nLoaded = true;
                loadNotifications();
            } else if (!open) {
                loadNotifications();
            }
        });
        document.addEventListener('click', function() {
            if (!nMenu.hidden) { nMenu.hidden = true; nTrigger.setAttribute('aria-expanded', 'false'); }
        });
        nMenu.addEventListener('click', function(e) { e.stopPropagation(); });
        
        // Initial load to check for red dot immediately on page load
        loadNotifications();
        nLoaded = true;

        // REAL-TIME AUTO REFRESH: Fetch notifications every 5 seconds
        setInterval(function() {
            // We fetch silently in the background. We check if the dropdown is hidden 
            // so we don't redraw the list while the user is trying to read/click it!
            if (nMenu && nMenu.hidden) {
                loadNotifications();
            }
        }, 5000);
    }

    // Sidebar: apply preload state immediately (avoids flicker) on desktop only
    if (!window.matchMedia('(max-width: 768px)').matches &&
        document.documentElement.classList.contains('dashboard-root-sidebar-collapsed-preload')) {
        var r = document.getElementById('dashboardRoot');
        if (r) r.classList.add('dashboard-root-sidebar-collapsed');
        document.documentElement.classList.remove('dashboard-root-sidebar-collapsed-preload');
    }

    // Sidebar hamburger toggle: state persists (localStorage) on desktop,
    // acts as overlay drawer on mobile.
    (function() {
        var root = document.querySelector('.dashboard-root');
        var sidebarToggle = document.getElementById('sidebarToggle');           // inside sidebar
        var topbarToggle = document.getElementById('topbarSidebarToggle');      // in top bar (mobile)
        var backdrop = document.getElementById('sidebarBackdrop');
        var storageKey = 'dashboard-sidebar-collapsed';
        if (!root) return;

        function isMobile() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        // Desktop: persistent collapsed state (do NOT apply on mobile so overlay shows full sidebar)
        var collapsed = false;
        if (!isMobile()) {
            collapsed = localStorage.getItem(storageKey) === '1';
            if (collapsed) root.classList.add('dashboard-root-sidebar-collapsed');
        }

        function handleToggleClick() {
            if (isMobile()) {
                // Ensure full sidebar when opening overlay
                root.classList.remove('dashboard-root-sidebar-collapsed');
                root.classList.toggle('dashboard-root-sidebar-open');
            } else {
                collapsed = root.classList.toggle('dashboard-root-sidebar-collapsed');
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            }
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', handleToggleClick);
        if (topbarToggle) topbarToggle.addEventListener('click', handleToggleClick);

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                root.classList.remove('dashboard-root-sidebar-open');
            });
        }
    })();

    // Mobile header: hide on scroll down, show on scroll up (disabled on desktop)
    (function() {
        var element = document.querySelector('.dashboard-topbar');
        if (!element) return;

        var className = 'dashboard-topbar-hidden';
        var offsetSelector = '[data-scroll-anchor]';
        var mediaQueryMatch = '(min-width: 48rem)';

        var offsetEl = element.querySelector(offsetSelector) || element;
        var mediaQuery = window.matchMedia(mediaQueryMatch);
        var isDisabled = mediaQuery.matches;
        var lastY = null;

        function getOffset() {
            return offsetEl.getBoundingClientRect().height || 0;
        }

        function onMediaChange(ev) {
            isDisabled = ev.matches;
            if (ev.matches) element.classList.remove(className);
        }

        function onScroll() {
            if (isDisabled) return;
            var scrollY = window.scrollY;
            var offset = getOffset();
            var isPastOffset = scrollY > offset;
            var isAtBottom = scrollY + window.innerHeight >= document.documentElement.scrollHeight;
            var direction = '';
            if (!isAtBottom && lastY !== null) {
                direction = scrollY > lastY ? 'down' : 'up';
            }
            element.classList.toggle(className, isPastOffset && direction === 'down');
            lastY = scrollY;
        }

        mediaQuery.addEventListener('change', onMediaChange);
        window.addEventListener('scroll', onScroll, { passive: true });
    })();

    // Shared "Columns" dropdown search for admin/dealer tables
    (function() {
        function normalizeText(value) {
            return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function ensureColumnsMenuReady(menu) {
            if (!menu || menu.dataset.columnsSearchReady === '1') return;
            menu.dataset.columnsSearchReady = '1';

            var title = menu.querySelector('.inquiries-columns-menu-title');
            if (!title) return;

            var searchWrap = document.createElement('div');
            searchWrap.className = 'inquiries-columns-search';

            var searchIcon = document.createElement('i');
            searchIcon.className = 'bi bi-search inquiries-columns-search-icon';
            searchIcon.setAttribute('aria-hidden', 'true');

            var searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'inquiries-columns-search-input';
            searchInput.placeholder = 'Type to search';
            searchInput.autocomplete = 'off';
            searchInput.setAttribute('aria-label', 'Search columns');

            searchWrap.appendChild(searchIcon);
            searchWrap.appendChild(searchInput);

            var emptyState = document.createElement('div');
            emptyState.className = 'inquiries-columns-empty';
            emptyState.textContent = 'No columns found';
            title.insertAdjacentElement('afterend', searchWrap);
            searchWrap.insertAdjacentElement('afterend', emptyState);

            menu._columnsSearchWrap = searchWrap;
            menu._columnsSearchInput = searchInput;
            menu._columnsEmptyState = emptyState;
            menu._columnsCheckItems = Array.prototype.slice.call(menu.querySelectorAll('.inquiries-columns-check'));

            searchInput.addEventListener('input', function() {
                applyColumnsTypeahead(menu, searchInput.value);
            });
        }

        function isMenuOpen(menu) {
            if (!menu) return false;
            if (menu.hidden) return false;
            return window.getComputedStyle(menu).display !== 'none';
        }

        function matchesColumnsTypeahead(text, keyword) {
            if (!keyword) return true;
            return text.indexOf(keyword) !== -1;
        }

        function applyColumnsTypeahead(menu, keyword) {
            ensureColumnsMenuReady(menu);
            if (!menu) return;

            var shown = 0;
            var normalizedKeyword = normalizeText(keyword);
            var checkItems = menu._columnsCheckItems || [];
            var emptyState = menu._columnsEmptyState;
            var firstMatch = null;

            menu.dataset.columnsTypeahead = normalizedKeyword;

            checkItems.forEach(function(item) {
                var matched = matchesColumnsTypeahead(normalizeText(item.textContent), normalizedKeyword);
                item.hidden = !matched;
                if (matched) {
                    shown += 1;
                    if (!firstMatch) firstMatch = item;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('is-visible', shown === 0);
            }

            if (normalizedKeyword && firstMatch) {
                firstMatch.scrollIntoView({ block: 'nearest' });
            }
        }

        function resetColumnsSearch(menu) {
            if (!menu) return;
            ensureColumnsMenuReady(menu);
            if (menu._columnsSearchInput) {
                menu._columnsSearchInput.value = '';
            }
            applyColumnsTypeahead(menu, '');
        }

        function closeColumnsMenu(menu) {
            if (!menu) return;
            menu.hidden = true;

            var dropdown = menu.closest('.inquiries-columns-dropdown');
            var button = dropdown ? dropdown.querySelector('button[aria-haspopup="true"]') : null;
            if (button) {
                button.setAttribute('aria-expanded', 'false');
                if (typeof button.focus === 'function') {
                    button.focus();
                }
            }
        }

        function getOpenColumnsMenus() {
            return Array.prototype.filter.call(
                document.querySelectorAll('.inquiries-columns-menu'),
                function(menu) { return isMenuOpen(menu); }
            );
        }

        function getActiveColumnsMenu() {
            var openMenus = getOpenColumnsMenus();
            return openMenus.length ? openMenus[openMenus.length - 1] : null;
        }

        function isTypingTarget(target) {
            if (!target) return false;
            var tagName = (target.tagName || '').toUpperCase();
            if (tagName === 'TEXTAREA' || tagName === 'SELECT' || !!target.isContentEditable) return true;
            if (tagName !== 'INPUT') return false;

            var inputType = String(target.type || '').toLowerCase();
            return ['text', 'search', 'email', 'number', 'tel', 'url'].indexOf(inputType) !== -1;
        }

        Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-menu'), function(menu) {
            ensureColumnsMenuReady(menu);

            menu.addEventListener('change', function(e) {
                if (!e.target || e.target.tagName !== 'INPUT' || e.target.type !== 'checkbox') return;
            });

            menu.addEventListener('click', function(e) {
                var resetButton = e.target.closest('.inquiries-columns-reset');
                if (!resetButton) return;

                window.setTimeout(function() {
                    resetColumnsSearch(menu);
                    closeColumnsMenu(menu);
                }, 0);
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-dropdown > button'), function(button) {
            button.addEventListener('click', function() {
                var menu = button.parentElement ? button.parentElement.querySelector('.inquiries-columns-menu') : null;
                if (!menu) return;

                window.setTimeout(function() {
                    resetColumnsSearch(menu);
                    if (menu._columnsSearchInput && typeof menu._columnsSearchInput.focus === 'function' && isMenuOpen(menu)) {
                        menu._columnsSearchInput.focus();
                    }
                }, 0);
            });
        });

        document.addEventListener('keydown', function(e) {
            var menu = getActiveColumnsMenu();
            if (!menu) return;

            if (e.key === 'Escape') {
                resetColumnsSearch(menu);
                return;
            }

            if (isTypingTarget(e.target)) return;

            if (e.key === 'Backspace') {
                e.preventDefault();
                ensureColumnsMenuReady(menu);
                if (menu._columnsSearchInput) {
                    menu._columnsSearchInput.focus();
                    menu._columnsSearchInput.value = (menu._columnsSearchInput.value || '').slice(0, -1);
                    applyColumnsTypeahead(menu, menu._columnsSearchInput.value);
                    return;
                }
                applyColumnsTypeahead(menu, (menu.dataset.columnsTypeahead || '').slice(0, -1));
                return;
            }

            if (e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) return;
            if (!/[a-z0-9 _-]/i.test(e.key)) return;

            e.preventDefault();
            ensureColumnsMenuReady(menu);
            if (menu._columnsSearchInput) {
                menu._columnsSearchInput.focus();
                menu._columnsSearchInput.value = (menu._columnsSearchInput.value || '') + e.key;
                applyColumnsTypeahead(menu, menu._columnsSearchInput.value);
                return;
            }
            applyColumnsTypeahead(menu, (menu.dataset.columnsTypeahead || '') + e.key);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.inquiries-columns-dropdown')) return;
            window.setTimeout(function() {
                Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-menu'), function(menu) {
                    if (!isMenuOpen(menu)) {
                        resetColumnsSearch(menu);
                    }
                });
            }, 0);
        });
    })();

    // Shared idle handling:
    // enforce client-side logout after the configured session lifetime
    var isLoggedIn = {{ session()->has('user_role') ? 'true' : 'false' }};
    if (isLoggedIn) {
        var lifetimeMinutes = {{ (int) config('session.lifetime', 120) }};
        var maxIdleMs = Math.max(1, lifetimeMinutes) * 60 * 1000;
        var storagePrefix = 'dashboard-idle.{{ session()->getId() }}.';
        var activityKey = storagePrefix + 'last-activity';

        function readStoredTime(key) {
            try {
                var raw = sessionStorage.getItem(key);
                var value = parseInt(raw, 10);
                return Number.isFinite(value) && value > 0 ? value : 0;
            } catch (e) {
                return 0;
            }
        }

        function writeStoredTime(key, value) {
            try {
                sessionStorage.setItem(key, String(value));
            } catch (e) {}
        }

        function removeStoredTime(key) {
            try {
                sessionStorage.removeItem(key);
            } catch (e) {}
        }

        var lastActivity = readStoredTime(activityKey) || Date.now();
        writeStoredTime(activityKey, lastActivity);

        function bump() {
            lastActivity = Date.now();
            writeStoredTime(activityKey, lastActivity);
        }

        ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(function(evt) {
            document.addEventListener(evt, bump, { passive: true });
        });

        setInterval(function() {
            var storedActivity = readStoredTime(activityKey);
            if (storedActivity > 0) {
                lastActivity = storedActivity;
            }

            if ((Date.now() - lastActivity) > maxIdleMs) {
                // Best-effort logout, then go to login.
                var token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
                if (token) {
                    fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).finally(function() {
                        window.location.href = '{{ route('login') }}';
                    });
                } else {
                    window.location.href = '{{ route('login') }}';
                }
            }
        }, 5000);
    }
})();
</script>
@endpush
@stack('scripts')
</body>
</html>
