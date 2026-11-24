<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-tags me-2 text-primary"></i>Categories &amp; SLAs</h1>
        <p class="page-subtitle">Set response targets and service teams for each request category.</p>
    </div>
    <a href="<?= site_url('admin/categories/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Category
    </a>
</div>

<div class="panel-card">
    <div class="panel-header">Service categories</div>
    <div class="table-responsive">
        <table class="table">
            <thead class="table-light">
                <tr><th>#</th><th>Category</th><th>Standard SLA</th><th>High-priority SLA</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= esc($c['name']) ?></td>
                <td><?= $c['sla_hours'] ?> hours</td>
                <td><?= number_format($c['sla_hours'] / 2, 1) ?> hours</td>
                <td>
                    <a href="<?= site_url('admin/categories/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
