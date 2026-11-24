<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-tags me-2 text-primary"></i><?= $category ? 'Edit Category' : 'Create Category' ?></h1>
        <p class="page-subtitle">Configure the standard SLA used to calculate request due dates.</p>
    </div>
    <a href="<?= site_url('admin/categories') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Categories</a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="<?= $category ? site_url('admin/categories/'.$category['id'].'/update') : site_url('admin/categories/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc(old('name', $category['name'] ?? '')) ?>" placeholder="e.g. IT, HR, Facilities" maxlength="100" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Standard SLA (hours)</label>
                <input type="number" name="sla_hours" class="form-control" value="<?= old('sla_hours', $category['sla_hours'] ?? 24) ?>" min="1" required>
                <div class="form-text">High-priority requests receive half of this time.</div>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-2"></i><?= $category ? 'Save Changes' : 'Create Category' ?>
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
