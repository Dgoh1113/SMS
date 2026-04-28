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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260427-03">
    <script src="{{ asset('js/passkey-registration.js') }}?v=20260427-02"></script>
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
                'group' => 'Must know first',
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
                'group' => 'Must know first',
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
                'group' => 'Must know first',
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
                'group' => 'Daily work',
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
                'group' => 'Daily work',
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
                'group' => 'Reports',
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
                'group' => 'Setup',
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
                'group' => 'Must know first',
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
                'group' => 'Must know first',
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
                'group' => 'Daily work',
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
                'group' => 'Daily work',
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
                'group' => 'Daily work',
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
                'group' => 'Reports',
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
                'group' => 'Dashboard',
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

    $guideTopicGroups = [];
    foreach ($guideTopics as $index => $topic) {
        $groupLabel = $topic['group'] ?? 'More guides';
        if (!isset($guideTopicGroups[$groupLabel])) {
            $guideTopicGroups[$groupLabel] = [];
        }

        $guideTopicGroups[$groupLabel][] = [
            'index' => $index,
            'topic' => $topic,
        ];
    }

    $guideTopicCount = count($guideTopics);
    $guideTopicTotalLabel = str_pad((string) $guideTopicCount, 2, '0', STR_PAD_LEFT);
    $firstGuideTopic = $guideTopics[0];
@endphp

<div class="dashboard-root" id="dashboardRoot">
    @if (in_array(session('user_role'), ['admin', 'manager'], true))
        @include('partials.sidebar-admin')
    @elseif (session('user_role') === 'dealer')
        @include('partials.sidebar-dealer')
    @endif
  

    <main class="dashboard-main">
        @include('partials.dashboard-topbar')

        @if (session('error'))
            <div class="login-message login-error" style="margin:16px;" data-flash-message="1">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin:16px;" data-flash-message="1">{{ session('success') }}</div>
        @endif

        <div class="dashboard-passkey-quick-modal" id="profilePasskeyQuickModal" hidden>
            <div class="dashboard-passkey-quick-card dashboard-passkey-manage-card" role="dialog" aria-modal="true" aria-labelledby="profilePasskeyQuickTitle">
                <button type="button" class="dashboard-passkey-quick-close" id="profilePasskeyQuickClose" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <h3 class="dashboard-passkey-quick-title" id="profilePasskeyQuickTitle">Manage passkeys</h3>
                <p class="dashboard-passkey-quick-subtitle">Add a new passkey for a trusted device, and remove old ones when you no longer need them.</p>
                <div class="dashboard-passkey-quick-actions dashboard-passkey-manage-actions">
                    <button type="button" class="login-primary-btn" id="profilePasskeyPhoneBtn" style="margin-top: 0;">
                        <i class="bi bi-phone" aria-hidden="true"></i>
                        <span>Use Phone / Scan QR</span>
                    </button>
                    <button type="button" class="login-passkey-btn" id="profilePasskeyDeviceBtn">
                        <i class="bi bi-laptop" aria-hidden="true"></i>
                        <span>Register On This Device</span>
                    </button>
                </div>

                <div class="dashboard-passkey-nickname-box" id="profilePasskeyNicknameBox" hidden>
                    <div class="dashboard-passkey-nickname-title" id="profilePasskeyNicknameTitle">Who will be using this passkey?</div>
                    <p class="dashboard-passkey-nickname-help" id="profilePasskeyNicknameHelp"></p>
                    <label class="dashboard-passkey-nickname-field" for="profilePasskeyNicknameInput">
                        <span>Passkey nickname</span>
                        <input type="text" id="profilePasskeyNicknameInput" maxlength="255" autocomplete="off" placeholder="My laptop">
                    </label>
                    <div class="dashboard-passkey-nickname-actions">
                        <button type="button" class="dashboard-passkey-secondary-btn" id="profilePasskeyNicknameCancel">Cancel</button>
                        <button type="button" class="login-primary-btn" id="profilePasskeyNicknameConfirm" style="margin-top: 0;">Continue</button>
                    </div>
                </div>

                <div class="dashboard-passkey-quick-status" id="profilePasskeyQuickStatus" hidden></div>

                <section class="dashboard-passkey-manage-section">
                    <div class="dashboard-passkey-manage-section-head">
                        <h4>Saved passkeys</h4>
                        <p>Keep at least one trusted passkey available for future sign-in.</p>
                    </div>
                    <div class="dashboard-passkey-manage-list-wrap" id="profilePasskeyListWrap">
                        <div class="dashboard-passkey-manage-empty">Loading passkeys...</div>
                    </div>
                </section>
            </div>
        </div>

        <div class="dashboard-passkey-quick-modal dashboard-passkey-access-modal" id="profilePasskeyAccessModal" hidden>
            <div class="dashboard-passkey-quick-card dashboard-passkey-access-card" role="dialog" aria-modal="true" aria-labelledby="profilePasskeyAccessTitle">
                <button type="button" class="dashboard-passkey-quick-close" id="profilePasskeyAccessClose" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <div class="dashboard-passkey-access-icon" aria-hidden="true">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h3 class="dashboard-passkey-quick-title" id="profilePasskeyAccessTitle">Access denied</h3>
                <p class="dashboard-passkey-quick-subtitle dashboard-passkey-access-message" id="profilePasskeyAccessMessage">Sign in using the first registered passkey to manage additional passkeys.</p>
                <div class="dashboard-passkey-access-actions">
                    <button type="button" class="dashboard-passkey-secondary-btn" id="profilePasskeyAccessAction">Close</button>
                </div>
            </div>
        </div>

        <div class="dashboard-guide-modal" id="guideCatalogModal" hidden>
            <div class="dashboard-guide-card" role="dialog" aria-modal="true" aria-labelledby="guideCatalogTitle">
                <button type="button" class="dashboard-guide-close" id="guideCatalogClose" aria-label="Close guide">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <div class="dashboard-guide-layout">
                    <aside class="dashboard-guide-nav">
                        <span class="dashboard-guide-nav-label">Guidebook</span>
                        <h3 class="dashboard-guide-nav-title">Topic catalogue</h3>
                        <div class="dashboard-guide-topic-groups">
                            @foreach ($guideTopicGroups as $groupLabel => $groupItems)
                                <section class="dashboard-guide-topic-group" aria-label="{{ $groupLabel }}">
                                    <div class="dashboard-guide-topic-group-label">{{ $groupLabel }}</div>
                                    <div class="dashboard-guide-topic-list">
                                        @foreach ($groupItems as $groupItem)
                                            @php
                                                $topic = $groupItem['topic'];
                                                $index = $groupItem['index'];
                                            @endphp
                                            <button
                                                type="button"
                                                class="dashboard-guide-topic-btn{{ $index === 0 ? ' is-active' : '' }}"
                                                data-guide-topic-index="{{ $index }}"
                                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                            >
                                                <span class="dashboard-guide-topic-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                                <span class="dashboard-guide-topic-copy">
                                                    <strong>{{ $topic['label'] }}</strong>
                                                    <small>{{ $topic['subtitle'] }}</small>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </aside>
                    <section class="dashboard-guide-content">
                        <article class="dashboard-guide-paper" id="guideCatalogPaper">
                            <div class="dashboard-guide-page-meta">
                                <span class="dashboard-guide-page-kicker" id="guideCatalogPageKicker">{{ $firstGuideTopic['group'] ?? 'Guidebook' }}</span>
                                <span class="dashboard-guide-page-count">Page <span id="guideCatalogPageNumber">01</span> / <span id="guideCatalogPageTotal">{{ $guideTopicTotalLabel }}</span></span>
                            </div>
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
                        </article>
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
    var guidePageKicker = document.getElementById('guideCatalogPageKicker');
    var guidePageNumber = document.getElementById('guideCatalogPageNumber');
    var guideTitle = document.getElementById('guideCatalogTitle');
    var guideIntro = document.getElementById('guideCatalogIntro');
    var guideStepsHeading = document.getElementById('guideCatalogStepsHeading');
    var guideSteps = document.getElementById('guideCatalogSteps');
    var guideTipsHeading = document.getElementById('guideCatalogTipsHeading');
    var guideTips = document.getElementById('guideCatalogTips');
    var guideNote = document.getElementById('guideCatalogNote');
    var guideContent = document.querySelector('.dashboard-guide-content');
    var guidePaper = document.getElementById('guideCatalogPaper');
    var guideDesktopParent = guideContent ? guideContent.parentNode : null;
    var guideDesktopNextSibling = guideContent ? guideContent.nextSibling : null;
    var guideMobileQuery = window.matchMedia ? window.matchMedia('(max-width: 768px)') : null;
    var guideTopicButtons = Array.prototype.slice.call(document.querySelectorAll('[data-guide-topic-index]'));
    var guideTopics = @json($guideTopics);
    var activeGuideTopicIndex = 0;
    var isGuideMobileContentOpen = false;
    var guideFlipTimer = null;
    var trigger = document.getElementById('profileDropdownTrigger');
    var menu = document.getElementById('profileDropdownMenu');
    var passkeyTrigger = document.getElementById('profileRegisterPasskeyBtn');
    var passkeyModal = document.getElementById('profilePasskeyQuickModal');
    var passkeyCloseBtn = document.getElementById('profilePasskeyQuickClose');
    var passkeyAccessModal = document.getElementById('profilePasskeyAccessModal');
    var passkeyAccessCloseBtn = document.getElementById('profilePasskeyAccessClose');
    var passkeyAccessActionBtn = document.getElementById('profilePasskeyAccessAction');
    var passkeyAccessMessage = document.getElementById('profilePasskeyAccessMessage');
    var passkeyDeviceBtn = document.getElementById('profilePasskeyDeviceBtn');
    var passkeyPhoneBtn = document.getElementById('profilePasskeyPhoneBtn');
    var passkeyStatus = document.getElementById('profilePasskeyQuickStatus');
    var passkeyListWrap = document.getElementById('profilePasskeyListWrap');
    var passkeyNicknameBox = document.getElementById('profilePasskeyNicknameBox');
    var passkeyNicknameTitle = document.getElementById('profilePasskeyNicknameTitle');
    var passkeyNicknameHelp = document.getElementById('profilePasskeyNicknameHelp');
    var passkeyNicknameInput = document.getElementById('profilePasskeyNicknameInput');
    var passkeyNicknameCancelBtn = document.getElementById('profilePasskeyNicknameCancel');
    var passkeyNicknameConfirmBtn = document.getElementById('profilePasskeyNicknameConfirm');
    var passkeyUtils = window.SQLSMSPasskey;
    var passkeyPendingPreference = null;
    var passkeyListUrl = '{{ route("passkey.manage.list") }}';
    var passkeyDeleteUrlTemplate = '{{ route("passkey.manage.delete", ['passkeyId' => '__PASSKEY_ID__']) }}';

    function hideProfileMenu() {
        if (!menu || !trigger) return;
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    }

    function syncDashboardOverlayLock() {
        var hasGuide = guideModal && !guideModal.hidden;
        var hasPasskey = passkeyModal && !passkeyModal.hidden;
        var hasPasskeyAccess = passkeyAccessModal && !passkeyAccessModal.hidden;
        document.body.classList.toggle('dashboard-passkey-modal-open', !!(hasGuide || hasPasskey || hasPasskeyAccess));
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

    function formatGuidePageNumber(index) {
        var pageNumber = Number(index) + 1;
        return pageNumber < 10 ? '0' + pageNumber : String(pageNumber);
    }

    function clearGuideFlipTimer() {
        if (guideFlipTimer) {
            window.clearTimeout(guideFlipTimer);
            guideFlipTimer = null;
        }
    }

    function clearGuideFlipState() {
        if (!guideContent) return;
        guideContent.classList.remove('is-flipping-out', 'is-flipping-in', 'is-flipping-forward', 'is-flipping-backward');
    }

    function isGuideMobileLayout() {
        return guideMobileQuery ? guideMobileQuery.matches : false;
    }

    function placeGuideContent() {
        if (!guideContent) return;

        if (isGuideMobileLayout()) {
            var activeButton = guideTopicButtons.find(function(button) {
                return Number(button.getAttribute('data-guide-topic-index')) === activeGuideTopicIndex;
            });

            if (activeButton && activeButton.parentNode && guideContent.previousElementSibling !== activeButton) {
                activeButton.insertAdjacentElement('afterend', guideContent);
            }

            guideContent.classList.add('is-mobile-inline');
            guideContent.hidden = !isGuideMobileContentOpen;
            return;
        }

        if (guideDesktopParent) {
            if (guideDesktopNextSibling && guideDesktopNextSibling.parentNode === guideDesktopParent) {
                guideDesktopParent.insertBefore(guideContent, guideDesktopNextSibling);
            } else {
                guideDesktopParent.appendChild(guideContent);
            }
        }

        guideContent.classList.remove('is-mobile-inline');
        guideContent.hidden = false;
    }

    function applyGuideTopic(topic, safeIndex) {
        if (guideHeroIcon) {
            guideHeroIcon.innerHTML = '<i class="bi ' + escapeGuideHtml(topic.icon || 'bi-bookmark-fill') + '" aria-hidden="true"></i>';
        }
        if (guidePageKicker) guidePageKicker.textContent = topic.group || topic.badge || 'Guidebook';
        if (guidePageNumber) guidePageNumber.textContent = formatGuidePageNumber(safeIndex);
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
    }

    function animateGuideTopicChange(topic, safeIndex, directionClass) {
        if (!guideContent || !guidePaper) {
            applyGuideTopic(topic, safeIndex);
            placeGuideContent();
            return;
        }

        clearGuideFlipTimer();
        clearGuideFlipState();
        guideContent.classList.add(directionClass, 'is-flipping-out');

        guideFlipTimer = window.setTimeout(function() {
            applyGuideTopic(topic, safeIndex);
            placeGuideContent();
            clearGuideFlipState();
            guideContent.classList.add(directionClass, 'is-flipping-in');

            guideFlipTimer = window.setTimeout(function() {
                clearGuideFlipState();
                guideFlipTimer = null;
            }, 360);
        }, 220);
    }

    function renderGuideTopic(index, mobileContentOpen) {
        if (!guideTopics || !guideTopics.length) {
            return;
        }

        var safeIndex = index >= 0 && index < guideTopics.length ? index : 0;
        var previousIndex = activeGuideTopicIndex;
        var topic = guideTopics[safeIndex];
        activeGuideTopicIndex = safeIndex;

        if (isGuideMobileLayout()) {
            if (typeof mobileContentOpen === 'boolean') {
                isGuideMobileContentOpen = mobileContentOpen;
            }
        } else {
            isGuideMobileContentOpen = true;
        }

        guideTopicButtons.forEach(function(button) {
            var buttonIndex = Number(button.getAttribute('data-guide-topic-index'));
            var isActive = buttonIndex === safeIndex;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-current', isActive ? 'true' : 'false');
            button.setAttribute('aria-expanded', isActive && isGuideMobileLayout() && isGuideMobileContentOpen ? 'true' : 'false');
        });

        if (!isGuideMobileLayout() && guideModal && !guideModal.hidden && safeIndex !== previousIndex) {
            animateGuideTopicChange(topic, safeIndex, safeIndex > previousIndex ? 'is-flipping-forward' : 'is-flipping-backward');
            return;
        }

        clearGuideFlipTimer();
        clearGuideFlipState();
        applyGuideTopic(topic, safeIndex);
        placeGuideContent();
    }

    function setGuideModalOpen(open) {
        if (!guideModal || !guideTrigger) return;
        guideModal.hidden = !open;
        guideTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            renderGuideTopic(activeGuideTopicIndex, false);
        } else {
            clearGuideFlipTimer();
            clearGuideFlipState();
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

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function parsePasskeyResponse(response) {
        return response.text().then(function(text) {
            var data = {};
            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                data = {};
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    }

    function buildPasskeyDeleteUrl(passkeyId) {
        return passkeyDeleteUrlTemplate.replace('__PASSKEY_ID__', encodeURIComponent(String(passkeyId || '')));
    }

    function setPasskeyButtonsDisabled(disabled) {
        [passkeyDeviceBtn, passkeyPhoneBtn, passkeyNicknameCancelBtn, passkeyNicknameConfirmBtn].forEach(function(button) {
            if (button) {
                button.disabled = !!disabled;
            }
        });

        if (passkeyNicknameInput) {
            passkeyNicknameInput.disabled = !!disabled;
        }

        if (passkeyListWrap) {
            passkeyListWrap.querySelectorAll('[data-passkey-delete-id]').forEach(function(button) {
                button.disabled = !!disabled;
            });
        }
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

    function renderPasskeyList(items) {
        if (!passkeyListWrap) return;

        if (!items || !items.length) {
            passkeyListWrap.innerHTML = '<div class="dashboard-passkey-manage-empty">No passkeys saved yet.</div>';
            return;
        }

        passkeyListWrap.innerHTML = [
            '<div class="dashboard-passkey-table-scroll">',
                '<table class="dashboard-passkey-table">',
                    '<thead><tr><th>Passkey ID</th><th>Nickname</th><th>Last use</th><th>Action</th></tr></thead>',
                    '<tbody>',
                        items.map(function(item) {
                            var passkeyId = item.user_passkeyid || item.id || '';
                            return [
                                '<tr>',
                                    '<td class="dashboard-passkey-table-id">#', escapeGuideHtml(passkeyId), '</td>',
                                    '<td>',
                                        '<div class="dashboard-passkey-table-nickname">', escapeGuideHtml(item.nickname), '</div>',
                                    '</td>',
                                    '<td class="dashboard-passkey-table-lastuse">', escapeGuideHtml(item.last_use_label || 'Never used'), '</td>',
                                    '<td class="dashboard-passkey-table-action">',
                                        '<button type="button" class="dashboard-passkey-delete-btn" data-passkey-delete-id="', escapeGuideHtml(passkeyId), '" data-passkey-delete-name="', escapeGuideHtml(item.nickname), '">Delete</button>',
                                    '</td>',
                                '</tr>',
                            ].join('');
                        }).join(''),
                    '</tbody>',
                '</table>',
            '</div>'
        ].join('');
    }

    function loadPasskeyList() {
        if (!passkeyListWrap) {
            return Promise.resolve();
        }

        passkeyListWrap.innerHTML = '<div class="dashboard-passkey-manage-empty">Loading passkeys...</div>';

        return fetch(passkeyListUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(parsePasskeyResponse)
        .then(function(result) {
            if (!result.ok) {
                throw new Error((result.data && result.data.error) ? result.data.error : 'Could not load passkeys.');
            }

            renderPasskeyList(result.data.items || []);
        })
        .catch(function(err) {
            passkeyListWrap.innerHTML = '<div class="dashboard-passkey-manage-empty is-error">' + escapeGuideHtml(err && err.message ? err.message : 'Could not load passkeys.') + '</div>';
        });
    }

    function requestOpenPasskeyManager(onSuccess) {
        return fetch(passkeyListUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(parsePasskeyResponse)
        .then(function(result) {
            if (!result.ok) {
                throw new Error((result.data && result.data.error) ? result.data.error : 'Access denied.');
            }

            if (typeof onSuccess === 'function') {
                onSuccess(result.data || {});
            }
        })
        .catch(function(err) {
            setPasskeyAccessModalOpen(true, err && err.message ? err.message : 'Access denied.');
        });
    }

    function setPasskeyAccessModalOpen(open, message) {
        if (!passkeyAccessModal) return;

        if (passkeyAccessMessage && typeof message === 'string' && message.trim() !== '') {
            passkeyAccessMessage.textContent = message;
        }

        passkeyAccessModal.hidden = !open;
        syncDashboardOverlayLock();
    }

    function getPasskeyPromptCopy(preference) {
        if (preference === 'phone') {
            return {
                title: 'Who will be using this passkey?',
                help: '',
                placeholder: 'My phone',
                defaultValue: 'My phone'
            };
        }

        return {
            title: 'Who will be using this passkey?',
            help: '',
            placeholder: 'My laptop',
            defaultValue: 'My laptop'
        };
    }

    function setPasskeyNicknamePromptOpen(open, preference) {
        if (!passkeyNicknameBox) return;

        passkeyNicknameBox.hidden = !open;
        passkeyPendingPreference = open ? (preference === 'phone' ? 'phone' : 'device') : null;

        if (!open) {
            if (passkeyNicknameInput) {
                passkeyNicknameInput.value = '';
            }
            return;
        }

        var copy = getPasskeyPromptCopy(passkeyPendingPreference);
        if (passkeyNicknameTitle) passkeyNicknameTitle.textContent = copy.title;
        if (passkeyNicknameHelp) passkeyNicknameHelp.textContent = copy.help;
        if (passkeyNicknameInput) {
            passkeyNicknameInput.value = copy.defaultValue;
            passkeyNicknameInput.setAttribute('placeholder', copy.placeholder);
            window.setTimeout(function() {
                passkeyNicknameInput.focus();
                passkeyNicknameInput.select();
            }, 30);
        }
    }

    function setPasskeyModalOpen(open) {
        if (!passkeyModal) return;
        passkeyModal.hidden = !open;
        if (open) {
            setPasskeyAccessModalOpen(false);
        }
        syncDashboardOverlayLock();

        if (open) {
            setPasskeyStatus('', '');
            setPasskeyButtonsDisabled(false);
            setPasskeyNicknamePromptOpen(false);
            loadPasskeyList();
            return;
        }

        setPasskeyStatus('', '');
        setPasskeyButtonsDisabled(false);
        setPasskeyNicknamePromptOpen(false);
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
        setPasskeyNicknamePromptOpen(true, preference);
    }

    function submitProfilePasskeyRegistration() {
        if (!window.PublicKeyCredential || !passkeyUtils) {
            setPasskeyStatus('error', 'Passkeys are not supported in this browser.');
            return;
        }

        if (!passkeyPendingPreference) {
            setPasskeyStatus('error', 'Choose where to save the passkey first.');
            return;
        }

        var nickname = passkeyNicknameInput ? String(passkeyNicknameInput.value || '').trim() : '';
        if (!nickname) {
            setPasskeyStatus('error', 'Please name this passkey before continuing.');
            if (passkeyNicknameInput) {
                passkeyNicknameInput.focus();
            }
            return;
        }

        setPasskeyStatus('', '');
        setPasskeyButtonsDisabled(true);

        passkeyUtils.register({
            preference: passkeyPendingPreference,
            optionsUrl: '{{ route("passkey.register.options") }}',
            verifyUrl: '{{ route("passkey.register.verify") }}',
            csrfToken: getCsrfToken(),
            getNickname: function () {
                return nickname;
            }
        })
        .then(function(result) {
            if (result && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            setPasskeyNicknamePromptOpen(false);
            setPasskeyButtonsDisabled(false);
            setPasskeyStatus('success', 'Passkey registered successfully.');
            return loadPasskeyList();
        })
        .catch(function(err) {
            setPasskeyButtonsDisabled(false);
            var message = passkeyErrorMessage(err);
            if (message) {
                setPasskeyStatus('error', message);
            }
        });
    }

    function deleteManagedPasskey(passkeyId, nickname) {
        if (!passkeyId) {
            return;
        }

            var confirmMessage = nickname
                ? 'Confirm Remove "' + nickname + '" as your login passkey?'
                : 'Confirm Remove this passkey as your login passkey?';
            if (!window.confirm(confirmMessage)) {
                return;
            }

        setPasskeyStatus('', '');
        setPasskeyButtonsDisabled(true);

        fetch(buildPasskeyDeleteUrl(passkeyId), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        })
        .then(parsePasskeyResponse)
        .then(function(result) {
            if (!result.ok) {
                throw new Error((result.data && result.data.error) ? result.data.error : 'Could not delete passkey.');
            }

            setPasskeyButtonsDisabled(false);
            setPasskeyStatus('success', (result.data && result.data.message) ? result.data.message : 'Passkey deleted successfully.');
            return loadPasskeyList();
        })
        .catch(function(err) {
            setPasskeyButtonsDisabled(false);
            setPasskeyStatus('error', err && err.message ? err.message : 'Could not delete passkey.');
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
            requestOpenPasskeyManager(function(result) {
                setPasskeyModalOpen(true);
                renderPasskeyList((result && result.items) || []);
            });
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
            var clickedIndex = Number(button.getAttribute('data-guide-topic-index'));

            if (isGuideMobileLayout()) {
                var isSameTopic = clickedIndex === activeGuideTopicIndex;
                renderGuideTopic(clickedIndex, isSameTopic ? !isGuideMobileContentOpen : true);
                return;
            }

            renderGuideTopic(clickedIndex, true);
        });
    });

    function handleGuideLayoutChange() {
        clearGuideFlipTimer();
        clearGuideFlipState();
        isGuideMobileContentOpen = !isGuideMobileLayout();
        renderGuideTopic(activeGuideTopicIndex, isGuideMobileContentOpen);
    }

    if (guideMobileQuery) {
        if (typeof guideMobileQuery.addEventListener === 'function') {
            guideMobileQuery.addEventListener('change', handleGuideLayoutChange);
        } else if (typeof guideMobileQuery.addListener === 'function') {
            guideMobileQuery.addListener(handleGuideLayoutChange);
        }
    }

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
            requestOpenPasskeyManager(function(result) {
                setGuideModalOpen(false);
                setPasskeyModalOpen(true);
                renderPasskeyList((result && result.items) || []);
            });
        });
    }

    if (passkeyCloseBtn) {
        passkeyCloseBtn.addEventListener('click', function() {
            setPasskeyModalOpen(false);
        });
    }

    if (passkeyAccessCloseBtn) {
        passkeyAccessCloseBtn.addEventListener('click', function() {
            setPasskeyAccessModalOpen(false);
        });
    }

    if (passkeyAccessActionBtn) {
        passkeyAccessActionBtn.addEventListener('click', function() {
            setPasskeyAccessModalOpen(false);
        });
    }

    if (passkeyAccessModal) {
        passkeyAccessModal.addEventListener('click', function(e) {
            if (e.target === passkeyAccessModal) {
                setPasskeyAccessModalOpen(false);
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

    if (passkeyNicknameCancelBtn) {
        passkeyNicknameCancelBtn.addEventListener('click', function() {
            setPasskeyNicknamePromptOpen(false);
            setPasskeyStatus('', '');
        });
    }

    if (passkeyNicknameConfirmBtn) {
        passkeyNicknameConfirmBtn.addEventListener('click', function() {
            submitProfilePasskeyRegistration();
        });
    }

    if (passkeyNicknameInput) {
        passkeyNicknameInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitProfilePasskeyRegistration();
            }
        });
    }

    if (passkeyListWrap) {
        passkeyListWrap.addEventListener('click', function(e) {
            var deleteBtn = e.target.closest('[data-passkey-delete-id]');
            if (!deleteBtn) {
                return;
            }

            e.preventDefault();
            deleteManagedPasskey(
                deleteBtn.getAttribute('data-passkey-delete-id'),
                deleteBtn.getAttribute('data-passkey-delete-name') || ''
            );
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && guideModal && !guideModal.hidden) {
            setGuideModalOpen(false);
        }
        if (e.key === 'Escape' && passkeyAccessModal && !passkeyAccessModal.hidden) {
            setPasskeyAccessModalOpen(false);
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
<script>
(function () {
    function collectExportHeadMarkup() {
        return Array.prototype.map.call(document.querySelectorAll('link[rel="stylesheet"], style'), function (node) {
            return node.outerHTML;
        }).join('\n');
    }

    function replaceExportCanvases(sourceRoot, cloneRoot) {
        var sourceCanvases = sourceRoot.querySelectorAll('canvas');
        var cloneCanvases = cloneRoot.querySelectorAll('canvas');

        Array.prototype.forEach.call(sourceCanvases, function (sourceCanvas, index) {
            var cloneCanvas = cloneCanvases[index];
            if (!cloneCanvas) return;

            var image = document.createElement('img');
            image.className = 'report-export-canvas-image';
            image.alt = '';

            try {
                image.src = sourceCanvas.toDataURL('image/png');
            } catch (error) {
                return;
            }

            var rect = sourceCanvas.getBoundingClientRect();
            if (rect.width > 0) {
                image.style.width = rect.width + 'px';
                image.style.maxWidth = '100%';
            } else {
                image.style.width = '100%';
            }
            image.style.height = 'auto';

            cloneCanvas.replaceWith(image);
        });
    }

    function pruneExportClone(root) {
        [
            '.reports-tabs-row',
            '.reports-period-row',
            '.reports-header',
            '.reports-header-actions',
            '.rv2-filtered-layer-head',
            '.rrp-filter-row',
            '.report-filter-actions',
            '[data-export-report-pdf]'
        ].forEach(function (selector) {
            root.querySelectorAll(selector).forEach(function (node) {
                node.remove();
            });
        });
    }

    function resolveExportTarget(button) {
        var selector = button.getAttribute('data-export-target') || '';
        if (selector) {
            try {
                var selected = document.querySelector(selector);
                if (selected) return selected;
            } catch (error) {}
        }

        return button.closest('.dashboard-content.reports-page, .reports-page, .rv2-page, .rrp-page');
    }

    function buildExportWindow(printWindow, title, generatedLabel) {
        var isMonthlyPerformance = title.indexOf('Monthly Performance Report') === 0;
        var isDealerPerformance = title.indexOf('Dealer Performance Report') === 0;
        var isDealerSalesOvertime = title.indexOf('Dealer Sales Overtime Report') === 0;
        var isDealerRevenueProduction = title.indexOf('Dealer Revenue Production Report') === 0;
        var printedBy = @json(session('user_alias') ?: session('user_email') ?: session('user_role') ?: 'User');
        var exportStyles = [
            '@page{size:A4 landscape;margin:8mm;}',
            'html,body{background:#fff;color:#0f172a;font-family:"Public Sans",sans-serif;}',
            'body{margin:0;-webkit-print-color-adjust:exact;print-color-adjust:exact;}',
            '.report-export-shell{padding:20px 24px 28px;}',
            '.report-export-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #e5e7eb;}',
            '.report-export-title{margin:0;font-size:24px;line-height:1.15;font-weight:800;color:#0f172a;}',
            '.report-export-meta{font-size:12px;font-weight:600;color:#64748b;text-align:right;}',
            '.report-export-content{background:#fff;}',
            '.report-export-content .dashboard-content.reports-page,.report-export-content .reports-page,.report-export-content .rv2-page,.report-export-content .rrp-page{padding:0 !important;margin:0 !important;width:auto !important;min-height:0 !important;zoom:1 !important;transform:none !important;background:#fff !important;overflow:visible !important;}',
            '.report-export-content .reports-page-layout{width:100% !important;padding:0 !important;zoom:1 !important;transform:none !important;gap:14px !important;}',
            '.report-export-content .dashboard-panel,.report-export-content .reports-product-section,.report-export-content .reports-inquiry-section,.report-export-content .reports-status-section,.report-export-content .dealer-reports-status-section,.report-export-content .rv2-panel,.report-export-content .rrp-panel,.report-export-content .rrp-metric-card{break-inside:avoid;page-break-inside:avoid;}',
            '.report-export-content .dashboard-topbar,.report-export-content .dashboard-sidebar,.report-export-content .dashboard-bottombar{display:none !important;}',
            '.report-export-canvas-image{display:block;max-width:100%;height:auto;}',
            '.report-export-monthly-performance .report-export-shell{box-sizing:border-box;padding:5mm 6mm !important;}',
            '.report-export-monthly-performance .report-export-heading{gap:10px;margin-bottom:8px;padding-bottom:6px;}',
            '.report-export-monthly-performance .report-export-title{font-size:18px;line-height:1.1;}',
            '.report-export-monthly-performance .report-export-meta{font-size:9px;}',
            '.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page){zoom:.86 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page) .reports-page-layout{gap:7px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page) .reports-metrics--admin{display:grid !important;grid-template-columns:repeat(8,minmax(0,1fr)) !important;gap:7px !important;margin:0 0 7px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page) .reports-metric-card--admin,.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page) .report-metric-link--admin{min-height:68px !important;padding:7px 8px !important;border-radius:10px !important;box-shadow:none !important;}',
            '.report-export-monthly-performance .report-export-content .reports-admin-metric-top{margin-bottom:7px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-admin-metric-link-indicator{display:none !important;}',
            '.report-export-monthly-performance .report-export-content .reports-metric-icon{width:28px !important;height:28px !important;border-radius:8px !important;font-size:14px !important;box-shadow:none !important;}',
            '.report-export-monthly-performance .report-export-content .reports-metric-value{font-size:21px !important;line-height:1 !important;margin:0 0 5px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-metric-label{font-size:9px !important;line-height:1.05 !important;letter-spacing:.08em !important;}',
            '.report-export-monthly-performance .report-export-content .reports-admin-metric-trend{font-size:8px !important;line-height:1.1 !important;margin-top:6px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-admin-metric-trend-icon{width:10px !important;height:10px !important;}',
            '.report-export-monthly-performance .report-export-content .dashboard-panels-two-column{display:grid !important;grid-template-columns:minmax(0,1.28fr) minmax(0,.88fr) !important;gap:7px !important;margin:0 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-section,.report-export-monthly-performance .report-export-content .reports-status-section,.report-export-monthly-performance .report-export-content .reports-product-section{border-radius:10px !important;box-shadow:none !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-section .dashboard-panel-header,.report-export-monthly-performance .report-export-content .reports-status-section .dashboard-panel-header,.report-export-monthly-performance .report-export-content .reports-product-section .dashboard-panel-header{padding:9px 10px 5px !important;gap:8px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-section .dashboard-panel-body,.report-export-monthly-performance .report-export-content .reports-status-section .dashboard-panel-body,.report-export-monthly-performance .report-export-content .reports-product-section .dashboard-panel-body{padding:0 10px 9px !important;}',
            '.report-export-monthly-performance .report-export-content .dashboard-panel-title{font-size:14px !important;line-height:1.1 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-subtitle,.report-export-monthly-performance .report-export-content .reports-status-subtitle,.report-export-monthly-performance .report-export-content .reports-product-subtitle{font-size:9px !important;line-height:1.15 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-chip{padding:5px 10px !important;font-size:9px !important;}',
            '.report-export-monthly-performance .report-export-content .dealer-reports-card,.report-export-monthly-performance .report-export-content .dealer-reports-status-card,.report-export-monthly-performance .report-export-content .reports-product-card{padding:7px !important;border-radius:9px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-section .dealer-reports-chart-wrapper{height:188px !important;min-height:188px !important;padding:0 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-inquiry-section .report-export-canvas-image{width:100% !important;height:188px !important;object-fit:contain !important;}',
            '.report-export-monthly-performance .report-export-content .admin-inquiry-trend-legend{margin-top:4px !important;gap:12px !important;font-size:9px !important;}',
            '.report-export-monthly-performance .report-export-content .report-status-body{display:grid !important;grid-template-columns:minmax(0,.78fr) minmax(0,1fr) !important;align-items:center !important;gap:8px !important;}',
            '.report-export-monthly-performance .report-export-content .dealer-reports-status-card{min-height:178px !important;display:flex !important;align-items:center !important;justify-content:center !important;border:0 !important;background:transparent !important;box-shadow:none !important;}',
            '.report-export-monthly-performance .report-export-content .report-donut-wrapper{height:168px !important;min-height:168px !important;display:flex !important;align-items:center !important;justify-content:center !important;}',
            '.report-export-monthly-performance .report-export-content .report-donut{width:146px !important;height:146px !important;filter:saturate(1.18) contrast(1.05);box-shadow:inset 0 0 0 1px rgba(15,23,42,.08),0 8px 18px rgba(15,23,42,.08) !important;}',
            '.report-export-monthly-performance .report-export-content .report-donut-center{inset:50% auto auto 50% !important;width:82px !important;height:82px !important;transform:translate(-50%,-50%) !important;display:flex !important;flex-direction:column !important;align-items:center !important;justify-content:center !important;gap:2px !important;text-align:center !important;box-shadow:0 3px 10px rgba(15,23,42,.06) !important;}',
            '.report-export-monthly-performance .report-export-content .report-donut-total{display:block !important;font-size:22px !important;line-height:.95 !important;margin:0 !important;}',
            '.report-export-monthly-performance .report-export-content .report-donut-label{display:block !important;font-size:8px !important;line-height:1 !important;margin:0 !important;}',
            '.report-export-monthly-performance .report-export-content .report-legend{display:grid !important;grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:4px 12px !important;margin:0 !important;font-size:9px !important;line-height:1.15 !important;}',
            '.report-export-monthly-performance .report-export-content .report-legend li{min-width:0 !important;gap:5px !important;}',
            '.report-export-monthly-performance .report-export-content .report-legend-color{width:8px !important;height:8px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-product-section{margin-top:7px !important;break-inside:auto !important;page-break-inside:auto !important;}',
            '.report-export-monthly-performance .report-export-content .reports-product-scale{gap:6px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-product-scale-chip{font-size:8px !important;padding:3px 7px !important;}',
            '.report-export-monthly-performance .report-export-content .reports-product-chart-wrapper{height:126px !important;min-height:126px !important;padding:0 !important;}',
            '.report-export-monthly-performance .report-export-content .reports-product-section .report-export-canvas-image{width:100% !important;height:126px !important;object-fit:contain !important;}',
            '.report-export-monthly-performance .report-export-content .dealer-reports-empty,.report-export-monthly-performance .report-export-content .reports-product-empty{font-size:10px !important;padding:28px 10px !important;}',
            '.report-export-dealer-performance .report-export-shell{box-sizing:border-box;padding:5mm 6mm !important;}',
            '.report-export-dealer-performance .report-export-heading{gap:10px;margin-bottom:8px;padding-bottom:6px;}',
            '.report-export-dealer-performance .report-export-title{font-size:18px;line-height:1.1;}',
            '.report-export-dealer-performance .report-export-meta{font-size:9px;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page{padding:0 !important;margin:0 !important;gap:7px !important;zoom:.86 !important;overflow:visible !important;background:#fff !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-header{display:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metrics{display:grid !important;grid-template-columns:repeat(7,minmax(0,1fr)) !important;gap:7px !important;margin:0 0 7px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metric-card{min-height:68px !important;padding:7px 8px !important;border-radius:10px !important;box-shadow:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metric-icon{width:28px !important;height:28px !important;margin:0 0 7px !important;border-radius:8px !important;font-size:14px !important;box-shadow:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metric-icon::before{display:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metric-value{font-size:21px !important;line-height:1 !important;margin:0 0 5px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-metric-label{font-size:9px !important;line-height:1.05 !important;letter-spacing:.08em !important;margin:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .dashboard-panels-two-column{display:grid !important;grid-template-columns:minmax(0,1.28fr) minmax(0,.88fr) !important;gap:7px !important;margin:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-section,.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-section,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-section{border-radius:10px !important;box-shadow:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-section .dashboard-panel-header,.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-section .dashboard-panel-header{padding:9px 10px 5px !important;gap:8px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-section .dashboard-panel-body,.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-section .dashboard-panel-body{padding:0 10px 9px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .dashboard-panel-title{font-size:14px !important;line-height:1.1 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-subtitle,.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-subtitle,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-subtitle{font-size:9px !important;line-height:1.15 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-chip{padding:5px 10px !important;font-size:9px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-card,.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-card,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-card{padding:7px !important;border-radius:9px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-section .dealer-reports-chart-wrapper{height:188px !important;min-height:188px !important;padding:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-inquiry-section .report-export-canvas-image{width:100% !important;height:188px !important;object-fit:contain !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-status-body{display:grid !important;grid-template-columns:minmax(0,.78fr) minmax(0,1fr) !important;align-items:center !important;gap:8px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-status-card{min-height:178px !important;display:flex !important;align-items:center !important;justify-content:center !important;border:0 !important;background:transparent !important;box-shadow:none !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-donut-wrapper{height:168px !important;min-height:168px !important;display:flex !important;align-items:center !important;justify-content:center !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-donut{width:146px !important;height:146px !important;filter:saturate(1.18) contrast(1.05);box-shadow:inset 0 0 0 1px rgba(15,23,42,.08),0 8px 18px rgba(15,23,42,.08) !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-donut-center{inset:50% auto auto 50% !important;width:82px !important;height:82px !important;transform:translate(-50%,-50%) !important;display:flex !important;flex-direction:column !important;align-items:center !important;justify-content:center !important;gap:2px !important;text-align:center !important;box-shadow:0 3px 10px rgba(15,23,42,.06) !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-donut-total{display:block !important;font-size:22px !important;line-height:.95 !important;margin:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-donut-label{display:block !important;font-size:8px !important;line-height:1 !important;margin:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-legend{display:grid !important;grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:4px 12px !important;margin:0 !important;padding:0 !important;font-size:9px !important;line-height:1.15 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-legend li{min-width:0 !important;gap:5px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .report-legend-color{width:8px !important;height:8px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-section{margin-top:7px !important;break-inside:auto !important;page-break-inside:auto !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-scale{gap:6px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-scale-chip{font-size:8px !important;padding:3px 7px !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-chart-wrapper{height:126px !important;min-height:126px !important;padding:0 !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-section .report-export-canvas-image{width:100% !important;height:126px !important;object-fit:contain !important;}',
            '.report-export-dealer-performance .report-export-content .dealer-reports-page .dealer-reports-empty,.report-export-dealer-performance .report-export-content .dealer-reports-page .reports-product-empty{font-size:10px !important;padding:28px 10px !important;}',
            '.report-export-dealer-sales-overtime .report-export-shell{box-sizing:border-box;padding:5mm 6mm !important;}',
            '.report-export-dealer-sales-overtime .report-export-heading{gap:10px;margin-bottom:8px;padding-bottom:6px;}',
            '.report-export-dealer-sales-overtime .report-export-title{font-size:18px;line-height:1.1;}',
            '.report-export-dealer-sales-overtime .report-export-meta{font-size:9px;}',
            '.report-export-dealer-revenue-production .report-export-shell{box-sizing:border-box;padding:5mm 6mm !important;}',
            '.report-export-dealer-revenue-production .report-export-heading{gap:10px;margin-bottom:8px;padding-bottom:6px;}',
            '.report-export-dealer-revenue-production .report-export-title{font-size:18px;line-height:1.1;}',
            '.report-export-dealer-revenue-production .report-export-meta{font-size:9px;}',
            '.report-export-dealer-revenue-production .report-export-content .rrp-page{zoom:.86 !important;}',
            '@media print{.report-export-shell{padding:0;}.report-export-monthly-performance .report-export-shell,.report-export-dealer-performance .report-export-shell,.report-export-dealer-sales-overtime .report-export-shell,.report-export-dealer-revenue-production .report-export-shell{padding:5mm 6mm !important;}.report-export-monthly-performance .report-export-heading,.report-export-dealer-performance .report-export-heading,.report-export-dealer-sales-overtime .report-export-heading,.report-export-dealer-revenue-production .report-export-heading{margin-bottom:6px;}.report-export-monthly-performance .report-export-content .reports-page:not(.dealer-reports-page),.report-export-dealer-performance .report-export-content .dealer-reports-page,.report-export-dealer-revenue-production .report-export-content .rrp-page{zoom:.76 !important;}}'
        ].join(' ');

        printWindow.document.open();
        printWindow.document.write('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title></title>' + collectExportHeadMarkup() + '<style>' + exportStyles + '</style></head><body><div class="report-export-shell"><div class="report-export-heading"><div class="report-export-title"></div><div class="report-export-meta"></div></div><div class="report-export-content"></div></div></body></html>');
        printWindow.document.close();
        printWindow.document.title = title;
        if (isMonthlyPerformance) {
            printWindow.document.body.classList.add('report-export-monthly-performance');
        }
        if (isDealerPerformance) {
            printWindow.document.body.classList.add('report-export-dealer-performance');
        }
        if (isDealerSalesOvertime) {
            printWindow.document.body.classList.add('report-export-dealer-sales-overtime');
        }
        if (isDealerRevenueProduction) {
            printWindow.document.body.classList.add('report-export-dealer-revenue-production');
        }
        printWindow.document.querySelector('.report-export-title').textContent = title;
        printWindow.document.querySelector('.report-export-meta').innerHTML =
            '<div>Generated on ' + generatedLabel + '</div>' +
            '<div>Printed by ' + printedBy + '</div>';
    }

    function exportReportPdf(trigger) {
        if (!trigger) return false;

        var target = resolveExportTarget(trigger);
        if (!target) {
            window.alert('Report content not found. Please refresh the page and try again.');
            return false;
        }

        var printFrame = document.createElement('iframe');
        printFrame.setAttribute('title', 'Report PDF export');
        printFrame.style.position = 'fixed';
        printFrame.style.left = '-10000px';
        printFrame.style.top = '0';
        printFrame.style.width = '1280px';
        printFrame.style.height = '900px';
        printFrame.style.opacity = '0';
        printFrame.style.pointerEvents = 'none';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);

        var printWindow = printFrame.contentWindow;
        if (!printWindow || !printWindow.document) {
            printFrame.remove();
            window.alert('Unable to prepare the report print view. Please refresh and try again.');
            return false;
        }

        var clone = target.cloneNode(true);
        pruneExportClone(clone);
        replaceExportCanvases(target, clone);

        var title = trigger.getAttribute('data-export-title') || document.title || 'Report';
        var generatedLabel = new Date().toLocaleString();

        buildExportWindow(printWindow, title, generatedLabel);

        var mount = printWindow.document.querySelector('.report-export-content');
        if (mount) {
            mount.appendChild(printWindow.document.importNode(clone, true));
        }

        var didTriggerPrint = false;
        var didCleanupFrame = false;
        var cleanupFrame = function () {
            if (didCleanupFrame) return;
            didCleanupFrame = true;
            window.setTimeout(function () {
                if (printFrame.parentNode) {
                    printFrame.parentNode.removeChild(printFrame);
                }
            }, 1000);
        };

        if (printWindow.addEventListener) {
            printWindow.addEventListener('afterprint', cleanupFrame, { once: true });
        }

        var triggerPrint = function () {
            if (didTriggerPrint) return;
            didTriggerPrint = true;
            window.setTimeout(function () {
                try {
                    printWindow.focus();
                    printWindow.print();
                } catch (error) {}
                window.setTimeout(cleanupFrame, 60000);
            }, 350);
        };

        printFrame.onload = triggerPrint;
        window.setTimeout(triggerPrint, 900);
        return false;
    }

    window.SQLSMSExportReportPdf = exportReportPdf;

    document.addEventListener('click', function (event) {
        var trigger = event.target && event.target.closest ? event.target.closest('[data-export-report-pdf]') : null;
        if (!trigger) return;

        event.preventDefault();
        exportReportPdf(trigger);
    });
})();
</script>
@endpush
@stack('scripts')
</body>
</html>
