<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-ticket-detailed me-2 text-primary"></i>All Requests</h1>
        <p class="page-subtitle">Review request ownership, priority, status, and upcoming SLA deadlines.</p>
    </div>
    <span class="badge rounded-pill bg-white text-secondary border"><?= count($requests) ?> requests</span>
</div>

<!-- Filters -->
<div class="panel-card mb-3">
    <div class="panel-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= esc($search) ?>"><?php endif; ?>
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">All statuses</option>
                <?php foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $statusValue => $label): ?>
                    <option value="<?= $statusValue ?>" <?= service('request')->getGet('status') === $statusValue ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="<?= site_url('admin/requests') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Employee</th>
                        <th>Agent</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">No requests match the selected filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                    <?php $overdue = \App\Models\RequestModel::isOverdue($r); ?>
                    <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                        <td class="text-muted">#<?= $r['id'] ?></td>
                        <td>
                            <a class="table-title" href="<?= site_url('requests/' . $r['id']) ?>"><?= esc($r['title']) ?></a>
                            <?php if ($overdue): ?>
                                <span class="badge badge-overdue text-white ms-1"><i class="bi bi-clock-fill"></i> Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($r['requester_name']) ?></td>
                        <td><?= esc($r['agent_name'] ?? 'Unassigned') ?></td>
                        <td><span class="badge rounded-pill badge-category"><?= esc($r['category_name']) ?></span></td>
                        <td><span class="priority-<?= esc($r['priority']) ?>"><?= esc(ucfirst($r['priority'])) ?></span></td>
                        <td><span class="badge rounded-pill bg-light text-dark status-<?= esc($r['status']) ?>"><?= esc(ucfirst(str_replace('_',' ',$r['status']))) ?></span></td>
                        <td class="<?= $overdue ? 'text-danger fw-bold' : '' ?>">
                            <?= $r['due_at'] ? date('M j, g:i A', strtotime($r['due_at'])) : '—' ?>
                        </td>
                        <td>
                            <a href="<?= site_url('requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
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
