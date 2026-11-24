<?php
$role = session()->get('role');
$path = uri_string();
$searchAction = match ($role) {
    'admin' => site_url('admin/requests'),
    'agent' => site_url('agent/requests'),
    default => site_url('my-requests'),
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Service Request Portal') ?> | Service Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0c2554;
            --navy-light: #173a78;
            --blue: #1769e8;
            --canvas: #f3f6fc;
            --ink: #14264b;
            --muted: #74819a;
            --line: #e6ebf4;
            --sidebar-width: 208px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--canvas); color: var(--ink); font-family: Inter, "Segoe UI", Arial, sans-serif; font-size: .92rem; }
        .app-shell { min-height: 100vh; }
        .sidebar {
            position: fixed; inset: 0 auto 0 0; z-index: 1020; display: flex; flex-direction: column;
            width: var(--sidebar-width); padding: 22px 13px 15px; color: #fff;
            background: linear-gradient(165deg, #102d65 0%, #091e45 100%);
            box-shadow: 5px 0 22px rgba(18, 38, 78, .12);
        }
        .brand { display: flex; align-items: center; gap: 11px; padding: 2px 9px 24px; color: #fff; text-decoration: none; }
        .brand-icon { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 12px; color: #b7d8ff; background: rgba(255,255,255,.1); font-size: 1.35rem; }
        .brand-name { display: block; font-size: 1.02rem; font-weight: 750; letter-spacing: -.025em; }
        .brand-subtitle { display: block; margin-top: 1px; color: #afbdd5; font-size: .68rem; }
        .sidebar-label { margin: 4px 11px 8px; color: #879bbc; font-size: .66rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: 12px; min-height: 41px; padding: 0 11px;
            border-radius: 9px; color: #c6d1e4; font-size: .82rem; font-weight: 550; text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar-nav .nav-link i { width: 18px; font-size: 1rem; text-align: center; }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active { color: #fff; background: #1260db; }
        .sidebar-nav .nav-link.active { box-shadow: 0 6px 14px rgba(0, 77, 201, .23); }
        .sidebar-divider { height: 1px; margin: 17px 7px; background: rgba(255,255,255,.12); }
        .sidebar-bottom { margin-top: auto; }
        .help-card { padding: 14px 12px; border: 1px solid rgba(255,255,255,.1); border-radius: 12px; background: rgba(255,255,255,.07); text-align: center; }
        .help-card-icon { display: inline-grid; width: 34px; height: 34px; margin-bottom: 5px; place-items: center; border-radius: 50%; color: #b7d8ff; background: rgba(255,255,255,.1); font-size: 1rem; }
        .help-card strong { display: block; font-size: .79rem; }
        .help-card small { display: block; margin: 3px 0 10px; color: #b7c5dd; font-size: .68rem; }
        .help-card .btn { width: 100%; padding: .42rem .5rem; border: 0; border-radius: 7px; background: #1769e8; font-size: .72rem; font-weight: 650; }
        .sign-out { display: flex; align-items: center; gap: 10px; margin: 12px 2px 0; padding: 8px 10px; color: #c6d1e4; font-size: .8rem; text-decoration: none; }
        .sign-out:hover { color: #fff; }
        .app-main { min-height: 100vh; margin-left: var(--sidebar-width); }
        .topbar {
            position: sticky; top: 0; z-index: 1010; display: flex; align-items: center; justify-content: space-between;
            min-height: 64px; padding: 0 27px; border-bottom: 1px solid var(--line); background: rgba(255,255,255,.96);
            backdrop-filter: blur(12px);
        }
        .top-search { position: relative; width: min(390px, 55%); }
        .top-search i { position: absolute; top: 50%; left: 13px; color: #7787a7; transform: translateY(-50%); }
        .top-search input { height: 36px; padding: 0 14px 0 36px; border: 1px solid #e1e7f2; border-radius: 10px; background: #f8faff; font-size: .76rem; }
        .top-search input:focus { border-color: #9ebcf2; background: #fff; box-shadow: 0 0 0 .2rem rgba(23,105,232,.08); }
        .account-chip { display: flex; align-items: center; gap: 9px; color: var(--ink); text-decoration: none; }
        .avatar { display: grid; width: 32px; height: 32px; place-items: center; border-radius: 50%; color: #fff; background: #2857a6; font-size: .72rem; font-weight: 700; }
        .account-name { font-size: .76rem; font-weight: 700; }
        .account-role { display: block; margin-top: 1px; color: var(--muted); font-size: .65rem; }
        .topbar-signout { display: none; color: #61708a; font-size: 1.05rem; }
        .main-content { max-width: 1600px; margin: 0 auto; padding: 25px 27px 36px; }
        .content-header { margin-bottom: 19px; }
        .content-header h1, .content-header h2, .content-header h3, .content-header h4 { color: var(--ink); }
        .page-title { margin: 0; font-size: 1.42rem; font-weight: 750; letter-spacing: -.035em; }
        .page-subtitle { margin: 4px 0 0; color: var(--muted); font-size: .81rem; }
        .card, .panel-card { border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 3px 12px rgba(28,48,88,.045); }
        .card-header, .panel-header { padding: 14px 16px; border-bottom: 1px solid var(--line); border-radius: 12px 12px 0 0 !important; background: #fff; color: var(--ink); font-size: .83rem; font-weight: 700; }
        .panel-body { padding: 16px; }
        .stat-card { height: 100%; border: 1px solid var(--line) !important; border-radius: 12px; box-shadow: 0 3px 12px rgba(28,48,88,.04); }
        .stat-card .card-body { display: flex; align-items: center; gap: 13px; padding: 15px; }
        .stat-icon { display: grid; flex: 0 0 46px; width: 46px; height: 46px; place-items: center; border-radius: 14px; font-size: 1.18rem; }
        .stat-value { color: var(--ink); font-size: 1.38rem; font-weight: 750; line-height: 1.15; }
        .stat-label { margin-top: 4px; color: #677590; font-size: .71rem; }
        .btn { border-radius: 8px; font-weight: 600; }
        .btn-primary { border-color: var(--blue); background: var(--blue); }
        .btn-primary:hover { border-color: #0d58cc; background: #0d58cc; }
        .form-control, .form-select { min-height: 39px; border-color: #dce3ef; border-radius: 8px; font-size: .84rem; }
        .form-control:focus, .form-select:focus { border-color: #8db4f5; box-shadow: 0 0 0 .2rem rgba(23,105,232,.1); }
        .form-label { color: #263757; font-size: .78rem; }
        .table { --bs-table-bg: transparent; margin-bottom: 0; color: #283a5d; font-size: .74rem; }
        .table > :not(caption) > * > * { padding: .7rem .75rem; border-bottom-color: #edf0f6; vertical-align: middle; }
        .table thead th { color: #75819a; background: #f7f9fd; font-size: .67rem; font-weight: 700; white-space: nowrap; }
        .table tbody tr:last-child td { border-bottom: 0; }
        .table tbody tr:hover { background: #f8faff; }
        .table-title { color: #1c3156; font-weight: 650; text-decoration: none; }
        .table-title:hover { color: var(--blue); }
        .status-open { color: #1769e8; }
        .status-in_progress { color: #bc7600; }
        .status-resolved { color: #148a68; }
        .status-closed { color: #64728b; }
        .priority-high { color: #d64045; font-weight: 700; }
        .priority-medium { color: #b87300; }
        .priority-low { color: #148a68; }
        .badge-overdue { background: #df4851; }
        .badge-category { color: #315681; background: #eaf2ff; }
        .content-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .flash-wrap { margin-bottom: 15px; }
        .alert { border: 0; border-radius: 10px; font-size: .82rem; }
        .request-filters { display: flex; flex-wrap: wrap; gap: 5px; }
        .request-filter { padding: .38rem .62rem; border: 0; border-radius: 7px; color: #64728b; background: transparent; font-size: .7rem; font-weight: 650; }
        .request-filter span { margin-left: 3px; color: #8a96aa; font-weight: 500; }
        .request-filter:hover, .request-filter.active { color: #fff; background: var(--blue); }
        .request-filter.active span { color: rgba(255,255,255,.82); }
        .sla-card { height: 100%; padding: 21px; border: 1px solid #dfe9fb; border-radius: 12px; background: linear-gradient(160deg, #fff 0%, #f1f6ff 100%); box-shadow: 0 3px 12px rgba(28,48,88,.045); }
        .sla-icon { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 12px; color: var(--blue); background: #e5efff; font-size: 1.15rem; }
        .sla-estimate { display: flex; flex-direction: column; gap: 5px; margin: 15px 0; padding: 13px; border: 1px solid #d8e6ff; border-radius: 9px; background: #fff; }
        .sla-estimate span { color: #6d7b95; font-size: .7rem; }
        .sla-estimate strong { color: #1d4d99; font-size: 1.15rem; }
        .sla-notes { display: grid; gap: 9px; padding-left: 18px; line-height: 1.5; }
        .status-chart-wrap { max-width: 210px; margin: 2px auto 13px; }
        .chart-legend { max-width: 245px; margin: 0 auto; }
        .activity-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #edf0f6; font-size: .73rem; }
        .activity-row:last-child { border-bottom: 0; }
        .activity-row a { color: #315681; text-decoration: none; }
        .activity-dot { display: grid; flex: 0 0 27px; width: 27px; height: 27px; place-items: center; border-radius: 50%; color: #2464c5; background: #eaf2ff; }
        .request-detail-label { color: var(--muted); font-size: .68rem; }
        .request-detail-value { margin-top: 3px; color: #24395e; font-size: .82rem; font-weight: 650; }
        .comment-bubble { padding: 12px 14px; border: 1px solid #e8edf5; border-radius: 10px; background: #f7f9fd; font-size: .8rem; }
        .date-pill { display: inline-flex; align-items: center; padding: 8px 12px; border: 1px solid var(--line); border-radius: 8px; color: #546683; background: #fff; font-size: .73rem; font-weight: 650; white-space: nowrap; }
        .request-activity-item { position: relative; padding: 0 0 17px 24px; border-left: 1px solid #e1e8f3; font-size: .75rem; }
        .request-activity-item:last-child { padding-bottom: 0; border-left-color: transparent; }
        .request-activity-item::before { position: absolute; top: 2px; left: -5px; width: 9px; height: 9px; border: 2px solid #fff; border-radius: 50%; background: #1f79eb; box-shadow: 0 0 0 1px #1f79eb; content: ""; }
        @media (max-width: 991.98px) {
            :root { --sidebar-width: 198px; }
            .topbar { padding: 0 20px; }
            .main-content { padding: 22px 20px 30px; }
        }
        @media (max-width: 767.98px) {
            .sidebar { position: relative; inset: auto; width: 100%; min-height: auto; padding: 11px 13px; }
            .brand { padding: 2px 7px 10px; }
            .sidebar-label, .sidebar-divider, .sidebar-bottom { display: none; }
            .sidebar-nav { flex-direction: row; overflow-x: auto; padding-bottom: 2px; }
            .sidebar-nav .nav-link { min-height: 37px; flex: 0 0 auto; padding: 0 10px; font-size: .72rem; }
            .sidebar-nav .nav-link i { width: auto; }
            .app-main { margin-left: 0; }
            .topbar { min-height: 56px; padding: 0 14px; }
            .top-search { width: min(270px, 58%); }
            .account-name, .account-role { display: none; }
            .topbar-signout { display: inline-flex; }
            .main-content { padding: 18px 14px 26px; }
            .page-title { font-size: 1.22rem; }
            .content-header { align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= site_url('dashboard') ?>">
            <span class="brand-icon"><i class="bi bi-headset"></i></span>
            <span><span class="brand-name">ServiceDesk</span><span class="brand-subtitle">Help Desk Portal</span></span>
        </a>

        <div class="sidebar-label">Workspace</div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <?php if ($role === 'employee'): ?>
                <a class="nav-link <?= $path === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
                    <i class="bi bi-house-door-fill"></i>Dashboard
                </a>
                <a class="nav-link <?= $path === 'requests/create' ? 'active' : '' ?>" href="<?= site_url('requests/create') ?>">
                    <i class="bi bi-plus-square"></i>New Request
                </a>
                <a class="nav-link <?= $path === 'my-requests' ? 'active' : '' ?>" href="<?= site_url('my-requests') ?>">
                    <i class="bi bi-card-list"></i>My Requests
                </a>
            <?php elseif ($role === 'agent'): ?>
                <a class="nav-link <?= $path === 'agent/requests' ? 'active' : '' ?>" href="<?= site_url('agent/requests') ?>">
                    <i class="bi bi-inbox"></i>Assigned Requests
                </a>
            <?php elseif ($role === 'admin'): ?>
                <a class="nav-link <?= $path === 'admin/dashboard' ? 'active' : '' ?>" href="<?= site_url('admin/dashboard') ?>">
                    <i class="bi bi-house-door-fill"></i>Dashboard
                </a>
                <a class="nav-link <?= $path === 'admin/requests' ? 'active' : '' ?>" href="<?= site_url('admin/requests') ?>">
                    <i class="bi bi-ticket-detailed"></i>All Requests
                </a>
                <a class="nav-link <?= str_starts_with($path, 'admin/users') ? 'active' : '' ?>" href="<?= site_url('admin/users') ?>">
                    <i class="bi bi-people"></i>Users
                </a>
                <a class="nav-link <?= str_starts_with($path, 'admin/categories') ? 'active' : '' ?>" href="<?= site_url('admin/categories') ?>">
                    <i class="bi bi-tags"></i>Categories &amp; SLAs
                </a>
                <a class="nav-link <?= $path === 'admin/activity-log' ? 'active' : '' ?>" href="<?= site_url('admin/activity-log') ?>">
                    <i class="bi bi-clock-history"></i>Activity Log
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-bottom">
            <div class="help-card">
                <span class="help-card-icon"><i class="bi bi-life-preserver"></i></span>
                <strong>Need help?</strong>
                <small><?= $role === 'employee' ? 'Our service teams are here for you.' : 'Keep your service queue moving.' ?></small>
                <?php if ($role === 'employee'): ?>
                    <a class="btn btn-primary" href="<?= site_url('requests/create') ?>">Create Request</a>
                <?php elseif ($role === 'agent'): ?>
                    <a class="btn btn-primary" href="<?= site_url('agent/requests') ?>">Open My Queue</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="<?= site_url('admin/requests') ?>">Manage Requests</a>
                <?php endif; ?>
            </div>
            <a class="sign-out" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-left"></i>Sign out</a>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <form class="top-search" action="<?= $searchAction ?>" method="get" role="search">
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="<?= esc(service('request')->getGet('q') ?? '') ?>" placeholder="Search requests..." aria-label="Search requests">
            </form>
            <div class="d-flex align-items-center gap-3">
                <div class="account-chip">
                    <span class="avatar"><?= esc(strtoupper(substr((string)session()->get('name'), 0, 1))) ?></span>
                    <span><span class="account-name"><?= esc(session()->get('name')) ?></span><span class="account-role"><?= esc(ucfirst($role)) ?></span></span>
                </div>
                <a class="topbar-signout" href="<?= site_url('logout') ?>" aria-label="Sign out"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </header>

        <main class="main-content">
            <div class="flash-wrap">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
