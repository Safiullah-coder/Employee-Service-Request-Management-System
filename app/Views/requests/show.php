<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$role = session()->get('role');
$statusFlow = \App\Models\RequestModel::STATUS_FLOW;
$nextStatus = null;
foreach ($statusFlow as $i => $s) {
    if ($s === $request['status'] && isset($statusFlow[$i+1])) {
        $nextStatus = $statusFlow[$i+1];
        break;
    }
}
?>

<div class="content-header">
    <div>
        <div class="text-uppercase text-muted fw-bold small mb-1">Request #<?= $request['id'] ?></div>
        <h1 class="page-title"><?= esc($request['title']) ?></h1>
        <p class="page-subtitle">Submitted by <?= esc($request['requester_name']) ?> · <?= esc($request['category_name']) ?></p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if ($isOverdue): ?><span class="badge badge-overdue text-white"><i class="bi bi-clock-fill me-1"></i>Overdue</span><?php endif; ?>
        <a href="<?= site_url('dashboard') ?>" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <section class="panel-card mb-3">
            <div class="panel-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-info-circle me-2 text-primary"></i>Request details</span>
                <span class="badge rounded-pill bg-light text-dark status-<?= esc($request['status']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $request['status']))) ?></span>
            </div>
            <div class="panel-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3"><div class="request-detail-label">Category</div><div class="request-detail-value"><span class="badge rounded-pill badge-category"><?= esc($request['category_name']) ?></span></div></div>
                    <div class="col-6 col-md-3"><div class="request-detail-label">Priority</div><div class="request-detail-value priority-<?= esc($request['priority']) ?>"><?= esc(ucfirst($request['priority'])) ?></div></div>
                    <div class="col-6 col-md-3"><div class="request-detail-label">Assigned agent</div><div class="request-detail-value"><?= esc($request['agent_name'] ?? 'Unassigned') ?></div></div>
                    <div class="col-6 col-md-3"><div class="request-detail-label">Due date</div><div class="request-detail-value <?= $isOverdue ? 'text-danger' : '' ?>"><?= $request['due_at'] ? date('M j, g:i A', strtotime($request['due_at'])) : '—' ?></div></div>
                </div>
                <?php if ($request['description']): ?>
                    <div class="border-top mt-3 pt-3">
                        <div class="request-detail-label mb-1">Description</div>
                        <div class="text-break"><?= nl2br(esc($request['description'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (($role === 'agent' || $role === 'admin') && $nextStatus): ?>
            <section class="panel-card mb-3">
                <div class="panel-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div><div class="fw-bold">Next step: <?= esc(ucfirst(str_replace('_', ' ', $nextStatus))) ?></div><div class="small text-muted">Move this request forward in the workflow.</div></div>
                <form action="<?= site_url('requests/' . $request['id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="<?= $nextStatus ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-2"></i>Update Status
                    </button>
                </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <section class="panel-card mb-3">
                <div class="panel-header"><i class="bi bi-person-check me-2 text-primary"></i>Assignment</div>
                <div class="panel-body">
                    <form action="<?= site_url('admin/requests/' . $request['id'] . '/reassign') ?>" method="POST" class="d-flex flex-wrap gap-2">
                    <?= csrf_field() ?>
                    <select name="assigned_to" class="form-select flex-grow-1" required>
                        <?php
                        foreach ($agents as $ag): ?>
                            <option value="<?= $ag['id'] ?>" <?= $request['assigned_to'] == $ag['id'] ? 'selected' : '' ?>>
                                <?= esc($ag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Save assignment</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel-card">
            <div class="panel-header"><i class="bi bi-chat-dots me-2 text-primary"></i>Comments <span class="text-muted fw-normal">(<?= count($comments) ?>)</span></div>
            <div class="panel-body">
                <?php if (empty($comments)): ?>
                    <p class="text-muted">No comments yet.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                    <div class="d-flex mb-3">
                        <div class="avatar me-3 flex-shrink-0">
                            <?= strtoupper(substr($c['user_name'],0,1)) ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="comment-bubble">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong><?= esc($c['user_name']) ?></strong>
                                    <small class="text-muted"><?= date('M j, g:i A', strtotime($c['created_at'])) ?></small>
                                </div>
                                <?= nl2br(esc($c['comment'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form action="<?= site_url('requests/' . $request['id'] . '/comment') ?>" method="POST" class="mt-3">
                    <?= csrf_field() ?>
                    <textarea name="comment" class="form-control mb-2" rows="3" maxlength="5000" placeholder="Write a comment or update..."></textarea>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Add Comment
                    </button>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-4 mt-3 mt-lg-0">
        <section class="panel-card">
            <div class="panel-header"><i class="bi bi-clock-history me-2 text-primary"></i>Activity timeline</div>
            <div class="panel-body">
                <?php if (empty($logs)): ?>
                    <p class="text-muted mb-0">No activity has been recorded yet.</p>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="request-activity-item">
                            <small class="text-muted d-block mb-1"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></small>
                            <strong><?= esc($log['user_name']) ?></strong>
                            <?php if ($log['field'] === 'comment'): ?>
                                added a comment
                            <?php else: ?>
                                updated <?= esc(str_replace('_', ' ', $log['field'])) ?>
                            <?php endif; ?>
                            <div class="text-muted mt-1 text-break"><?= esc(mb_strimwidth($log['new_value'] ?? '', 0, 100, '…')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?= $this->endSection() ?>
