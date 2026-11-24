<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusCounts = array_fill_keys(\App\Models\RequestModel::STATUS_FLOW, 0);
foreach ($requests as $requestItem) {
    $statusCounts[$requestItem['status']]++;
}
?>
<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-card-list me-2 text-primary"></i>My Requests</h1>
        <p class="page-subtitle">Track the status and service-level target for every request you submitted.</p>
    </div>
    <a href="<?= site_url('requests/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Request
    </a>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="request-filters" role="group" aria-label="Filter requests by status">
            <button type="button" class="request-filter active" data-status="all">All <span><?= count($requests) ?></span></button>
            <?php foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $status => $label): ?>
                <button type="button" class="request-filter" data-status="<?= $status ?>"><?= $label ?> <span><?= $statusCounts[$status] ?></span></button>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($search)): ?>
            <a href="<?= site_url('my-requests') ?>" class="small text-decoration-none">Clear search</a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">You have not submitted any requests yet. <a href="<?= site_url('requests/create') ?>">Create your first request</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                    <?php $overdue = \App\Models\RequestModel::isOverdue($r); ?>
                    <tr data-request-status="<?= esc($r['status']) ?>">
                        <td class="text-muted">#<?= $r['id'] ?></td>
                        <td>
                            <a class="table-title" href="<?= site_url('requests/' . $r['id']) ?>"><?= esc($r['title']) ?></a>
                            <?php if ($overdue): ?>
                                <span class="badge badge-overdue text-white ms-1"><i class="bi bi-clock-fill"></i> Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge rounded-pill badge-category"><?= esc($r['category_name']) ?></span></td>
                        <td><span class="priority-<?= esc($r['priority']) ?>"><?= esc(ucfirst($r['priority'])) ?></span></td>
                        <td>
                            <span class="badge rounded-pill bg-light text-dark status-<?= esc($r['status']) ?>">
                                <?= esc(ucfirst(str_replace('_',' ',$r['status']))) ?>
                            </span>
                        </td>
                        <td class="<?= $overdue ? 'text-danger fw-bold' : '' ?>">
                            <?= $r['due_at'] ? date('M j, g:i A', strtotime($r['due_at'])) : '—' ?>
                        </td>
                        <td>
                            <a href="<?= site_url('requests/' . $r['id']) ?>" class="btn btn-sm btn-light border" aria-label="View request #<?= $r['id'] ?>">
                                <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.request-filter').forEach((filter) => {
    filter.addEventListener('click', () => {
        document.querySelectorAll('.request-filter').forEach((item) => item.classList.remove('active'));
        filter.classList.add('active');
        const status = filter.dataset.status;
        document.querySelectorAll('[data-request-status]').forEach((row) => {
            row.hidden = status !== 'all' && row.dataset.requestStatus !== status;
        });
    });
});
</script>
<?= $this->endSection() ?>
