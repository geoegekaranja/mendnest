<?php
/* Preferred-caregiver picker: one selectable card per available caregiver.
   Before including this file, set:
     $caregivers         rows from available_caregivers()
     $selectedCaregiver  int: the chosen caregiver id (0 = no preference)
   The patient only states a preference. The admin makes the final assignment. */
if (!function_exists('caregiver_photo_url')) {
    require_once __DIR__ . '/accounts.php';
}
$selectedCaregiver = (int)($selectedCaregiver ?? 0);
?>
<style>
.cg-card { display:block; height:100%; padding:.9rem 1rem; border:2px solid #dee2e6; border-radius:12px;
           background:#fff; cursor:pointer; transition:border-color .15s, box-shadow .15s; }
.cg-card:hover { border-color:#198754; }
.btn-check:checked + .cg-card { border-color:#198754; box-shadow:0 0 0 3px rgba(25,135,84,.2); background:#f3fbf6; }
.cg-spec { display:inline-block; margin:.2rem 0; padding:.15rem .65rem; border-radius:20px;
           background:#e8f5ee; color:#146c43; font-size:.8rem; font-weight:600; }
.cg-avatar { width:52px; height:52px; border-radius:50%; object-fit:cover; flex:none; }
.cg-initial { background:#198754; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; }
</style>

<label class="d-block fw-semibold mt-3 mb-1">
    Preferred caregiver <span class="text-muted fw-normal">(optional)</span>
</label>
<p class="small text-muted mb-2">Pick someone you would like to care for you. Our team makes the final assignment and confirms it.</p>

<div class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="radio" class="btn-check" name="caregiver_id" id="cg_0" value="0"
               <?= $selectedCaregiver === 0 ? 'checked' : '' ?>>
        <label class="cg-card" for="cg_0">
            <strong>No preference</strong>
            <div class="small text-muted">We will assign the best available caregiver for your needs.</div>
        </label>
    </div>

    <?php foreach ($caregivers as $cg): ?>
        <?php $pic = caregiver_photo_url($cg['photo'] ?? null); ?>
        <div class="col-md-6">
            <input type="radio" class="btn-check" name="caregiver_id"
                   id="cg_<?= (int)$cg['id'] ?>" value="<?= (int)$cg['id'] ?>"
                   <?= $selectedCaregiver === (int)$cg['id'] ? 'checked' : '' ?>>
            <label class="cg-card" for="cg_<?= (int)$cg['id'] ?>">
                <div class="d-flex gap-2">
                    <?php if ($pic): ?>
                        <img src="<?= h($pic) ?>" alt="" class="cg-avatar">
                    <?php else: ?>
                        <span class="cg-avatar cg-initial"><?= h(mb_strtoupper(mb_substr((string)$cg['name'], 0, 1))) ?></span>
                    <?php endif; ?>
                    <div>
                        <strong><?= h($cg['name']) ?></strong><br>
                        <span class="cg-spec"><?= h($cg['specialization']) ?></span>
                        <?php if (!empty($cg['years_experience'])): ?>
                            <span class="small text-muted"> &middot; <?= (int)$cg['years_experience'] ?> yrs</span>
                        <?php endif; ?>
                        <?php if (!empty($cg['qualification'])): ?>
                            <div class="small text-muted"><?= h($cg['qualification']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($cg['languages'])): ?>
                            <div class="small text-muted">Speaks: <?= h($cg['languages']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($cg['bio'])): ?>
                            <div class="small text-muted mt-1"><?= h($cg['bio']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </label>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!$caregivers): ?>
    <p class="small text-muted">No caregivers are listed as available right now. We will assign one for you.</p>
<?php endif; ?>