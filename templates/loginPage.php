<?php $pageTitle = 'Authentication'; ?>
<div class="login_form__container">
  <div class="card login_form__form">
    <header>
      <h4>Authentication</h4>
    </header>
    <form method="post">
      <?= $csrfField ?>
      <?php if (!empty($error)): ?>
      <p class="text-error"><?= $e($error) ?></p>
      <?php endif; ?>
      <?php if (($failedAttempts ?? 0) > 0): ?>
      <p class="text-error">Failed login attempts: <?= $e($failedAttempts) ?></p>
      <?php endif; ?>

      <input type="hidden" name="next" value="<?= $e($next) ?>" />

      <p>
        <label for="input__text">Email</label>
        <input id="input__text" type="text" name="email" value="<?= $e($email ?? '') ?>"
          <?php if (!empty($error)): ?>class="error"<?php endif; ?> placeholder="Enter admin email" />
      </p>
      <p>
        <label for="input__password">Password</label>
        <input id="input__password" type="password" name="password"
          <?php if (!empty($error)): ?>class="error"<?php endif; ?> placeholder="Enter admin password" />
      </p>
      <p><button type="submit">Sign in</button></p>
    </form>
  </div>
</div>
