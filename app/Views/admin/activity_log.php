<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-clock-history me-2 text-primary"></i>Activity Log</h1>
        <p class="page-subtitle">An auditable timeline of request status, assignment, and comment changes.</p>
    </div>
</div>

<div class="panel-card">
    <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr><th>Date &amp; time</th><th>User</th><th>Request</th><th>Change</th><th>Previous value</th><th>New value</th></tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No activity has been recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= date('M j, Y · g:i A', strtotime($log['created_at'])) ?></small></td>
                        <td><?= esc($log['user_name']) ?></td>
                        <td><a href="<?= site_url('requests/' . $log['request_id']) ?>">#<?= $log['request_id'] ?> <?= esc(substr($log['request_title'] ?? '', 0, 30)) ?></a></td>
                        <td><span class="badge bg-primary"><?= esc(ucfirst(str_replace('_', ' ', $log['field']))) ?></span></td>
                        <td><span class="text-danger"><?= esc($log['old_value'] ?? '—') ?></span></td>
                        <td><span class="text-success fw-bold"><?= esc($log['new_value'] ?? '—') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>
