<?php $pageTitle = 'Admins'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Admins</h1>

      <div class="row">
        <div class="col">
          <a href="/admins/create" class="button primary outline">Create admin</a>
        </div>
      </div>

      <table class="striped">
        <thead>
          <tr>
            <th>Email</th>
            <th>Name</th>
            <th>Global admin</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $admin): ?>
          <tr>
            <td>
              <a href="/admins/<?= $e($admin->username) ?>/general"><?= $e($admin->username) ?></a>
            </td>
            <td><?= $e($admin->name) ?></td>
            <td><?= $localize($admin->isGlobalAdmin) ?></td>
            <td><?= $e($admin->isMailboxAdmin ? 'Mailbox' : 'Standalone') ?></td>
            <td><?= $localize($admin->active) ?></td>
            <td>
              <a href="/admins/<?= $e($admin->username) ?>/general" class="button primary outline">Edit</a>
              <form method="post" action="/admins/<?= $e($admin->username) ?>/delete" style="display:inline" onsubmit="return confirm('Delete admin <?= $e($admin->username) ?>?')">
                <?= $csrfField ?>
                <button type="submit" class="button error outline">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
