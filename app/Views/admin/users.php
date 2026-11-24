<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>User Management</h1>
        <p class="page-subtitle">Manage portal access and assign agents to their service category.</p>
    </div>
    <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Add User
    </a>
</div>

<div class="panel-card">
    <div class="panel-header">Portal users</div>
    <div class="table-responsive">
        <table class="table">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Service category</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td class="fw-semibold"><?= esc($u['name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td>
                    <?php $colors = ['admin'=>'danger','agent'=>'warning','employee'=>'primary']; ?>
                    <span class="badge bg-<?= $colors[$u['role']] ?? 'secondary' ?>"><?= $u['role'] === 'admin' ? 'Administrator' : ucfirst($u['role']) ?></span>
                </td>
                <td><?= esc($u['category_name'] ?? '—') ?></td>
                <td>
                    <a href="<?= site_url('admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary me-1" aria-label="Edit user">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="<?= site_url('admin/users/' . $u['id'] . '/delete') ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this user?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
