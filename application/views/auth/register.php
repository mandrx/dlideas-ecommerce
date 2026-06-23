<?= form_open('register/post') ?>
<div class="mb-3">
    <label for="full_name" class="form-label">Full Name</label>
    <input type="text" class="form-control" id="full_name" name="full_name"
           value="<?= set_value('full_name') ?>" required>
</div>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= set_value('email') ?>" required>
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Phone (optional)</label>
    <input type="text" class="form-control" id="phone" name="phone"
           value="<?= set_value('phone') ?>">
</div>
<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
</div>
<div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm Password</label>
    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
</div>
<button type="submit" class="btn btn-success w-100">Create Account</button>
<?= form_close() ?>
<p class="text-center mt-3"><a href="<?= base_url('login') ?>">Already have an account?</a></p>
