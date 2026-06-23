<?= form_open('login/post') ?>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= set_value('email') ?>" required>
</div>
<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
</div>
<button type="submit" class="btn btn-primary w-100">Login</button>
<?= form_close() ?>
<hr>
<p class="text-center">
    <a href="<?= base_url('forgot-password') ?>">Forgot password?</a> &bull;
    <a href="<?= base_url('register') ?>">Create account</a>
</p>
