<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-<?= $user ? 'gear' : 'plus' ?> me-2 text-primary"></i><?= $user ? 'Edit User' : 'Create User' ?></h1>
        <p class="page-subtitle">Configure the user's portal role and service-team access.</p>
    </div>
    <a href="<?= site_url('admin/users') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="<?= $user ? site_url('admin/users/'.$user['id'].'/update') : site_url('admin/users/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc(old('name', $user['name'] ?? '')) ?>" maxlength="100" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'] ?? '')) ?>" maxlength="150" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password <?= $user ? '(leave blank to keep the current password)' : '' ?></label>
                <input type="password" name="password" class="form-control" <?= !$user ? 'required' : '' ?>>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" id="user-role" class="form-select" required>
                    <?php $role = old('role', $user['role'] ?? 'employee'); ?>
                    <option value="employee" <?= $role === 'employee' ? 'selected' : '' ?>>Employee</option>
                    <option value="agent" <?= $role === 'agent' ? 'selected' : '' ?>>Agent</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>
            <div class="mb-4 <?= $role === 'agent' ? '' : 'd-none' ?>" id="agent-category-field">
                <label class="form-label fw-semibold">Service category</label>
                <select name="category_id" id="agent-category" class="form-select">
                    <option value="">Select the category this agent handles</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= old('category_id', $user['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Requests are automatically assigned to an agent in their matching category.</div>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-2"></i><?= $user ? 'Save Changes' : 'Create User' ?>
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const userRole = document.getElementById('user-role');
const agentCategory = document.getElementById('agent-category-field');
const categorySelect = document.getElementById('agent-category');
function toggleAgentCategory() {
    const isAgent = userRole.value === 'agent';
    agentCategory.classList.toggle('d-none', !isAgent);
    categorySelect.required = isAgent;
}
userRole.addEventListener('change', toggleAgentCategory);
toggleAgentCategory();
</script>
<?= $this->endSection() ?>
