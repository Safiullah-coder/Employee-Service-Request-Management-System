<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Welcome back, <?= esc(explode(' ', (string)session()->get('name'))[0]) ?>! <span aria-hidden="true">👋</span></h1>
        <p class="page-subtitle">Here's what's happening with your service requests.</p>
    </div>
    <span class="date-pill"><i class="bi bi-calendar3 me-2"></i>Month to date</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <span class="stat-icon" style="color:#1769e8;background:#e8f0ff"><i class="bi bi-folder2-open"></i></span>
                <div><div class="stat-value"><?= $stats['open'] ?></div><div class="stat-label">My Open Requests</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <span class="stat-icon" style="color:#159b76;background:#dcf7ed"><i class="bi bi-check-circle-fill"></i></span>
                <div><div class="stat-value"><?= $stats['resolved_this_month'] ?></div><div class="stat-label">Resolved This Month</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <span class="stat-icon" style="color:#e59a10;background:#fff0d2"><i class="bi bi-clock-fill"></i></span>
                <div><div class="stat-value"><?= $stats['overdue'] ?></div><div class="stat-label">Overdue Requests</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <span class="stat-icon" style="color:#8057df;background:#eee8ff"><i class="bi bi-hourglass-split"></i></span>
                <div><div class="stat-value"><?= $stats['average_resolution_hours'] !== null ? $stats['average_resolution_hours'] . 'h' : '—' ?></div><div class="stat-label">Avg. Resolution Time</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <section class="panel-card h-100">
            <div class="panel-header"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Request Overview</div>
            <div class="panel-body">
                <div class="row align-items-center">
                    <div class="col-7">
                        <div style="max-width:220px;margin:auto"><canvas id="requestStatusChart" aria-label="Requests by status"></canvas></div>
                    </div>
                    <div class="col-5">
                        <?php foreach ($statusChart as $index => $item): ?>
                            <?php $colors = ['#1976e8', '#ffb52e', '#26b88a', '#e84e55']; ?>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 small">
                                <span class="text-nowrap"><i class="bi bi-circle-fill me-1" style="color:<?= $colors[$index] ?>;font-size:.52rem"></i><?= esc($item['label']) ?></span>
                                <strong><?= $item['total'] ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="panel-card h-100">
            <div class="panel-header d-flex align-items-center justify-content-between gap-2">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</span>
                <a href="<?= site_url('my-requests') ?>" class="small text-primary text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Due date</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentRequests)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No requests yet. Create one to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentRequests as $request): ?>
                            <?php $isOverdue = \App\Models\RequestModel::isOverdue($request); ?>
                            <tr>
                                <td class="text-muted">#<?= $request['id'] ?></td>
                                <td><a class="table-title" href="<?= site_url('requests/' . $request['id']) ?>"><?= esc(mb_strimwidth($request['title'], 0, 28, '…')) ?></a></td>
                                <td><span class="badge rounded-pill badge-category"><?= esc($request['category_name']) ?></span></td>
                                <td><span class="priority-<?= esc($request['priority']) ?>"><?= esc(ucfirst($request['priority'])) ?></span></td>
                                <td><span class="badge rounded-pill bg-light text-dark status-<?= esc($request['status']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $request['status']))) ?></span></td>
                                <td class="<?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?> text-nowrap"><?= $request['due_at'] ? date('M j, g:i A', strtotime($request['due_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-12">
        <?php if ($overdueRequest): ?>
            <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-3 mb-0">
                <div><strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Overdue request</strong><br>
                    <span class="small">Request #<?= $overdueRequest['id'] ?> — <?= esc($overdueRequest['title']) ?> is past its SLA deadline.</span>
                </div>
                <a class="btn btn-danger btn-sm" href="<?= site_url('requests/' . $overdueRequest['id']) ?>">View details <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php else: ?>
            <div class="alert alert-primary d-flex align-items-center gap-2 mb-0" style="background:#edf4ff;color:#315581">
                <i class="bi bi-info-circle-fill"></i><span class="small">Your requests are on track. We'll highlight any missed SLA deadlines here.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const statusCanvas = document.getElementById('requestStatusChart');
new Chart(statusCanvas, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($statusChart, 'label'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        datasets: [{
            data: <?= json_encode(array_map('intval', array_column($statusChart, 'total'))) ?>,
            backgroundColor: ['#1976e8', '#ffb52e', '#26b88a', '#e84e55'],
            borderWidth: 0,
            hoverOffset: 4,
        }],
    },
    options: {
        cutout: '72%',
        plugins: { legend: { display: false } },
        maintainAspectRatio: true,
    },
    plugins: [{
        id: 'centerLabel',
        afterDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const total = <?= array_sum($counts) ?>;
            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#14264b';
            ctx.font = '700 22px Inter, Arial, sans-serif';
            ctx.fillText(total, x, y);
            ctx.fillStyle = '#74819a';
            ctx.font = '11px Inter, Arial, sans-serif';
            ctx.fillText('Total Requests', x, y + 18);
            ctx.restore();
        },
    }],
});
</script>
<?= $this->endSection() ?>
