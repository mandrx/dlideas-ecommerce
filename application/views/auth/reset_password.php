<?= form_open('reset-password/' . $token . '/post') ?>
<div class="mb-3">
    <label for="password" class="form-label">New Password</label>
    <input type="password" class="form-control" id="password" name="password" required minlength="8">
</div>
<div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm New Password</label>
    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
</div>
<button type="submit" class="btn btn-primary w-100">Update Password</button>
<?= form_close() ?>
