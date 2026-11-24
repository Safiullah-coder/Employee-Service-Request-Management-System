<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><i class="bi bi-plus-square me-2 text-primary"></i>New Service Request</h1>
        <p class="page-subtitle">Tell us what you need and the right service team will take it from here.</p>
    </div>
    <a href="<?= site_url('my-requests') ?>" class="btn btn-light border">
        <i class="bi bi-arrow-left me-1"></i>Cancel
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-header"><i class="bi bi-pencil-square me-2 text-primary"></i>Request details</div>
            <div class="panel-body p-lg-4">
                <form action="<?= site_url('requests/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="request-title">Title <span class="text-danger">*</span></label>
                        <input id="request-title" type="text" name="title" class="form-control" maxlength="200" value="<?= old('title') ?>" placeholder="e.g. Laptop charger is damaged" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" for="request-category">Category <span class="text-danger">*</span></label>
                            <select id="request-category" name="category_id" class="form-select" required>
                                <option value="">Choose a service team</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" data-sla-hours="<?= $cat['sla_hours'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= esc($cat['name']) ?> (<?= $cat['sla_hours'] ?>h SLA)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" for="request-priority">Priority <span class="text-danger">*</span></label>
                            <select id="request-priority" name="priority" class="form-select" required>
                                <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= old('priority', 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="request-description">Description</label>
                        <textarea id="request-description" name="description" class="form-control" rows="6" placeholder="Describe the issue and any steps already taken."><?= old('description') ?></textarea>
                        <div class="form-text">Include details that will help the service team resolve your request.</div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 border-top pt-3">
                        <a href="<?= site_url('my-requests') ?>" class="btn btn-light border px-3">Cancel</a>
                        <button type="submit" class="btn btn-primary px-3"><i class="bi bi-send me-2"></i>Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <aside class="sla-card">
            <span class="sla-icon"><i class="bi bi-clock-history"></i></span>
            <h2 class="h6 fw-bold mt-3">Service-level target</h2>
            <p class="small text-muted mb-3">Your due date is calculated automatically from the category SLA and request priority.</p>
            <div class="sla-estimate">
                <span><i class="bi bi-hourglass-split me-2 text-primary"></i>Estimated response time</span>
                <strong id="sla-estimate">Select a category</strong>
            </div>
            <ul class="sla-notes small text-muted mb-0">
                <li>High priority receives half of the standard SLA.</li>
                <li>Requests are routed to an agent for the selected category.</li>
                <li>You'll be able to follow status updates and comments.</li>
            </ul>
        </aside>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const categorySelect = document.getElementById('request-category');
const prioritySelect = document.getElementById('request-priority');
const slaEstimate = document.getElementById('sla-estimate');
function updateSlaEstimate() {
    const option = categorySelect.options[categorySelect.selectedIndex];
    const standardHours = Number(option.dataset.slaHours || 0);
    if (!standardHours) {
        slaEstimate.textContent = 'Select a category';
        return;
    }
    const hours = prioritySelect.value === 'high' ? standardHours / 2 : standardHours;
    slaEstimate.textContent = `${Number.isInteger(hours) ? hours : hours.toFixed(1)} hours`;
}
categorySelect.addEventListener('change', updateSlaEstimate);
prioritySelect.addEventListener('change', updateSlaEstimate);
updateSlaEstimate();
</script>
<?= $this->endSection() ?>
