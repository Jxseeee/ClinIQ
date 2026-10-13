<?php
$profilePhotoUrl = profileImageUrl($profileImagePath ?? null, $basePath);
$profilePhotoInputId = 'profile_image_' . ($profilePhotoRole ?? 'account');
?>
<section class="settings-section profile-photo-settings">
    <div class="settings-section-heading">
        <span class="settings-section-icon"><?= studentDashboardIcon('user') ?></span>
        <div>
            <h2>Profile photo</h2>
            <p>Use a clear square image. JPG, PNG, and WEBP files up to 3MB are supported.</p>
        </div>
    </div>

    <?php if (!empty($photoErrors)): ?>
        <div class="alert alert-danger">
            <ul class="settings-error-list">
                <?php foreach ($photoErrors as $photoError): ?>
                    <li><?= htmlspecialchars($photoError) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="settings-profile-layout">
        <div class="settings-profile-preview" data-profile-preview>
            <?php if ($profilePhotoUrl): ?>
                <img src="<?= htmlspecialchars($profilePhotoUrl) ?>"
                     alt="<?= htmlspecialchars($profilePhotoName) ?> profile photo"
                     class="settings-profile-image">
            <?php else: ?>
                <span class="settings-profile-placeholder" aria-hidden="true"><?= htmlspecialchars($profilePhotoInitial) ?></span>
            <?php endif; ?>
        </div>

        <div class="settings-profile-controls">
            <form method="POST" enctype="multipart/form-data" class="profile-photo-form" data-profile-photo-form>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="action" value="upload_profile_image">
                <input type="file" id="<?= htmlspecialchars($profilePhotoInputId) ?>" name="profile_image"
                       accept="image/jpeg,image/png,image/webp" required>
                <label for="<?= htmlspecialchars($profilePhotoInputId) ?>" class="settings-file-picker">
                    <?= studentDashboardIcon('camera') ?>
                    <span>Choose photo</span>
                </label>
                <span class="settings-profile-file-name" aria-live="polite">No file selected</span>
                <button type="submit" class="btn btn-success settings-profile-submit">Save photo</button>
            </form>

            <?php if ($profilePhotoUrl): ?>
                <form method="POST" class="profile-photo-remove-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="action" value="remove_profile_image">
                    <button type="submit" class="btn btn-secondary">Remove photo</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>