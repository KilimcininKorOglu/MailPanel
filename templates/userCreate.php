<?php $pageTitle = 'User'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Create user</h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domains"><?= $e($domain) ?></a> /
          <a href="/<?= $e($domain) ?>/users">Users</a> /
          <span class="text-light">Create user</span>
        </div>
      </div>

      <div class="row">
        <div class="col-8 col-6-md">
          <?php if (!empty($error)): ?>
          <p class="text-error"><?= $e($error) ?></p>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
          <p class="text-success"><?= $e($success) ?></p>
          <?php endif; ?>

          <form method="post" autocomplete="off">
            <p>
              <label for="uid">Identifier</label>
              <input id="uid" type="text" name="uid" required
                <?php if (!empty($validationErrors['uid'])): ?>class="error"<?php endif; ?>
                value="<?= $e($user->uid ?? '') ?>"
              />
              <?php if (!empty($validationErrors['uid'])): ?>
              <p class="text-error"><?= $e($validationErrors['uid']) ?></p>
              <?php endif; ?>
            </p>

            <p>
              <label for="password">Password</label>
              <input name="password" type="password" id="password" required autocomplete="new-password"
                <?php if (!empty($validationErrors['password'])): ?>class="error"<?php endif; ?>
              />
              <?php if (!empty($validationErrors['password'])): ?>
              <p class="text-error"><?= $e($validationErrors['password']) ?></p>
              <?php endif; ?>
            </p>
            <p>
              <label for="password_repeat">Password (repeat)</label>
              <input name="password_repeat" type="password" id="password_repeat" required
                <?php if (!empty($validationErrors['password_repeat'])): ?>class="error"<?php endif; ?>
              />
              <?php if (!empty($validationErrors['password_repeat'])): ?>
              <p class="text-error"><?= $e($validationErrors['password_repeat']) ?></p>
              <?php endif; ?>
            </p>

            <p>
              <label for="mailQuota">Quota, MB</label>
              <input
                id="mailQuota"
                name="mailQuota"
                type="number"
                value="<?= $e($user->mailQuota ?? 100) ?>"
                required
              />
            </p>
            <p>
              <button type="submit" class="button primary">
                Save
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
