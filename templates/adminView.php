<?php $pageTitle = $e($admin->username); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1><?= $e($admin->username) ?></h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/admins">Admins</a> /
          <span class="text-light"><?= $e($admin->username) ?></span>
        </div>
      </div>

      <?php if (!empty($error)): ?>
      <p class="text-error"><?= $e($error) ?></p>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
      <p class="text-success"><?= $e($success) ?></p>
      <?php endif; ?>

      <nav class="tabs">
        <a <?php if ($editMode === 'general'): ?>class="active"<?php endif; ?>
           href="/admins/<?= $e($admin->username) ?>/general">
          General
        </a>
        <a <?php if ($editMode === 'password'): ?>class="active"<?php endif; ?>
           href="/admins/<?= $e($admin->username) ?>/password">
          Password
        </a>
        <a <?php if ($editMode === 'domains'): ?>class="active"<?php endif; ?>
           href="/admins/<?= $e($admin->username) ?>/domains">
          Managed domains
        </a>
      </nav>

      <?php if ($editMode === 'general'): ?>
      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="name">Display name</label>
          <input id="name" type="text" name="name"
            value="<?= $e($admin->name) ?>"
          />
        </p>

        <p>
          <label>
            <input type="checkbox" name="isGlobalAdmin" <?php if ($admin->isGlobalAdmin): ?>checked<?php endif; ?> />
            Global administrator
          </label>
        </p>

        <p>
          <label>
            <input type="checkbox" name="active" <?php if ($admin->active): ?>checked<?php endif; ?> />
            Active
          </label>
        </p>

        <p class="text-light">
          Type: <?= $e($admin->isMailboxAdmin ? 'Mailbox admin' : 'Standalone admin') ?> |
          Created: <?= $e($admin->created ?? 'N/A') ?>
        </p>

        <button type="submit" class="button primary">Save changes</button>
      </form>

      <?php elseif ($editMode === 'password'): ?>
      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="password">New password</label>
          <input id="password" type="password" name="password" required
            <?php if (!empty($validationErrors['password'])): ?>class="error"<?php endif; ?>
          />
          <?php if (!empty($validationErrors['password'])): ?>
          <span class="text-error"><?= $e($validationErrors['password']) ?></span>
          <?php endif; ?>
        </p>

        <p>
          <label for="password_repeat">Repeat password</label>
          <input id="password_repeat" type="password" name="password_repeat" required
            <?php if (!empty($validationErrors['password_repeat'])): ?>class="error"<?php endif; ?>
          />
          <?php if (!empty($validationErrors['password_repeat'])): ?>
          <span class="text-error"><?= $e($validationErrors['password_repeat']) ?></span>
          <?php endif; ?>
        </p>

        <button type="submit" class="button primary">Change password</button>
      </form>

      <?php elseif ($editMode === 'domains'): ?>
      <h3>Managed domains</h3>

      <?php if (!empty($managedDomains)): ?>
      <table class="striped">
        <thead>
          <tr>
            <th>Domain</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($managedDomains as $managedDomain): ?>
          <tr>
            <td><?= $e($managedDomain) ?></td>
            <td>
              <form method="post" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="revoke" />
                <input type="hidden" name="domain" value="<?= $e($managedDomain) ?>" />
                <button type="submit" class="button error outline">Revoke</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p class="text-light">No domains assigned.</p>
      <?php endif; ?>

      <h3>Assign domain</h3>
      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="assign" />
        <div class="row">
          <div class="col-8">
            <select name="domain">
              <?php foreach ($allDomainNames as $domainName): ?>
                <?php if (!in_array($domainName, $managedDomains, true)): ?>
                <option value="<?= $e($domainName) ?>"><?= $e($domainName) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4">
            <button type="submit" class="button primary outline">Assign</button>
          </div>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
