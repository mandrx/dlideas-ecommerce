<p class="text-muted">Enter your email and we'll send a reset link.</p>
<?= form_open('forgot-password/post') ?>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" required>
</div>
<button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
<?= form_close() ?>
<p class="text-center mt-3"><a href="<?= base_url('login') ?>">Back to Login</a></p>
