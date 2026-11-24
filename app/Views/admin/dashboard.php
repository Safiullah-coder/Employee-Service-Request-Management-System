<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Welcome back, <?= esc(explode(' ', (string)session()->get('name'))[0]) ?>!</h1>
        <p class="page-subtitle">Here's what's happening across your service desk.</p>
    </div>
    <a class="btn btn-primary" href="<?= site_url('admin/requests') ?>"><i class="bi bi-ticket-detailed me-2"></i>View all requests</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card"><div class="card-body">
            <span class="stat-icon" style="color:#1769e8;background:#e8f0ff"><i class="bi bi-folder2-open"></i></span>
            <div><div class="stat-value"><?= $counts['open'] + $counts['in_progress'] ?></div><div class="stat-label">Active Requests</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card"><div class="card-body">
            <span class="stat-icon" style="color:#159b76;background:#dcf7ed"><i class="bi bi-check-circle-fill"></i></span>
            <div><div class="stat-value"><?= $counts['resolved'] ?></div><div class="stat-label">Resolved Requests</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card"><div class="card-body">
            <span class="stat-icon" style="color:#df4650;background:#ffe8e9"><i class="bi bi-exclamation-circle-fill"></i></span>
            <div><div class="stat-value"><?= $counts['overdue'] ?></div><div class="stat-label">Overdue Requests</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card"><div class="card-body">
            <span class="stat-icon" style="color:#8057df;background:#eee8ff"><i class="bi bi-collection-fill"></i></span>
            <div><div class="stat-value"><?= $counts['total'] ?></div><div class="stat-label">Total Requests</div></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <section class="panel-card h-100">
            <div class="panel-header"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Request Overview</div>
            <div class="panel-body">
                <div class="status-chart-wrap"><canvas id="requestStatusChart" aria-label="Requests by status"></canvas></div>
                <div class="chart-legend">
                    <?php $statusColors = ['open' => '#1976e8', 'in_progress' => '#ffb52e', 'resolved' => '#26b88a', 'closed' => '#e84e55']; ?>
                    <?php foreach (\App\Models\RequestModel::STATUS_FLOW as $status): ?>
                        <div class="d-flex justify-content-between align-items-center small py-1">
                            <span><i class="bi bi-circle-fill me-2" style="color:<?= $statusColors[$status] ?>;font-size:.52rem"></i><?= esc(ucfirst(str_replace('_', ' ', $status))) ?></span>
                            <strong><?= $counts[$status] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="category-summary mt-3 pt-3 border-top">
                    <div class="small fw-bold mb-2">Requests by category</div>
                    <?php if (empty($chart)): ?>
                        <div class="text-muted small">No requests to summarize yet.</div>
                    <?php else: ?>
                        <?php foreach ($chart as $category): ?>
                            <div class="d-flex justify-content-between small py-1">
                                <span><?= esc($category['category_name']) ?></span><span class="text-muted"><?= $category['total'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="panel-card">
            <div class="panel-header d-flex align-items-center justify-content-between gap-2">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</span>
                <a href="<?= site_url('admin/requests') ?>" class="small text-primary text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>#</th><th>Title</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentRequests)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No requests have been submitted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentRequests as $request): ?>
                            <tr>
                                <td class="text-muted">#<?= $request['id'] ?></td>
                                <td><a class="table-title" href="<?= site_url('requests/' . $request['id']) ?>"><?= esc(mb_strimwidth($request['title'], 0, 27, '…')) ?></a></td>
                                <td><?= esc($request['requester_name']) ?></td>
                                <td><span class="badge rounded-pill badge-category"><?= esc($request['category_name']) ?></span></td>
                                <td><span class="priority-<?= esc($request['priority']) ?>"><?= esc(ucfirst($request['priority'])) ?></span></td>
                                <td><span class="badge rounded-pill bg-light text-dark status-<?= esc($request['status']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $request['status']))) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($overdueRequest): ?>
            <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 mb-0">
                <div><strong><i class="bi bi-exclamation-triangle-fill me-2"></i>1 overdue request</strong><br>
                    <span class="small">#<?= $overdueRequest['id'] ?> — <?= esc($overdueRequest['title']) ?></span>
                </div>
                <a href="<?= site_url('requests/' . $overdueRequest['id']) ?>" class="btn btn-danger btn-sm">View details <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php endif; ?>

        <section class="panel-card mt-3">
            <div class="panel-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-activity me-2 text-primary"></i>Recent Activity</span>
                <a href="<?= site_url('admin/activity-log') ?>" class="small text-primary text-decoration-none">Activity log <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="panel-body py-2">
                <?php if (empty($recentLogs)): ?>
                    <div class="text-muted small py-2">No recent activity.</div>
                <?php else: ?>
                    <?php foreach (array_slice($recentLogs, 0, 4) as $log): ?>
                        <div class="activity-row">
                            <span class="activity-dot"><i class="bi bi-arrow-repeat"></i></span>
                            <span class="flex-grow-1"><strong><?= esc($log['user_name']) ?></strong> updated
                                <a href="<?= site_url('requests/' . $log['request_id']) ?>">#<?= $log['request_id'] ?> <?= esc(mb_strimwidth($log['request_title'] ?? '', 0, 30, '…')) ?></a>
                            </span>
                            <small class="text-muted text-nowrap"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const statusCanvas = document.getElementById('requestStatusChart');
new Chart(statusCanvas, {
    type: 'doughnut',
    data: {
        labels: ['Open', 'In Progress', 'Resolved', 'Closed'],
        datasets: [{
            data: <?= json_encode([$counts['open'], $counts['in_progress'], $counts['resolved'], $counts['closed']]) ?>,
            backgroundColor: ['#1976e8', '#ffb52e', '#26b88a', '#e84e55'],
            borderWidth: 0,
            hoverOffset: 4,
        }],
    },
    options: { cutout: '72%', plugins: { legend: { display: false } } },
    plugins: [{
        id: 'centerLabel',
        afterDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#14264b';
            ctx.font = '700 22px Inter, Arial, sans-serif';
            ctx.fillText(<?= (int)$counts['total'] ?>, x, y);
            ctx.fillStyle = '#74819a';
            ctx.font = '11px Inter, Arial, sans-serif';
            ctx.fillText('Total Requests', x, y + 18);
            ctx.restore();
        },
    }],
});
</script>
<?= $this->endSection() ?>
