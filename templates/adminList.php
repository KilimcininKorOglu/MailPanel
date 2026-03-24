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

      <form method="post" action="/admins/bulk">
        <?= $csrfField ?>
      <table class="striped">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll" onclick="document.querySelectorAll('input[name=\\'selectedAdmins[]\\']').forEach(c=>c.checked=this.checked)" /></th>
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
            <td><input type="checkbox" name="selectedAdmins[]" value="<?= $e($admin->username) ?>" /></td>
            <td>
              <a href="/admins/<?= $e($admin->username) ?>/general"><?= $e($admin->username) ?></a>
            </td>
            <td><?= $e($admin->name) ?></td>
            <td><?= $localize($admin->isGlobalAdmin) ?></td>
            <td><?= $e($admin->isMailboxAdmin ? 'Mailbox' : 'Standalone') ?></td>
            <td><?= $localize($admin->active) ?></td>
            <td>
              <a href="/admins/<?= $e($admin->username) ?>/general" class="button primary outline">Edit</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top: 0.5rem;">
        <select name="action" required>
          <option value="">-- Bulk action --</option>
          <option value="enable">Enable selected</option>
          <option value="disable">Disable selected</option>
          <option value="delete">Delete selected</option>
        </select>
        <button type="submit" class="button outline" onclick="return this.form.action.value==='delete' ? confirm('Delete selected admins?') : true">Apply</button>
      </div>
      </form>
    </div>
  </div>
</div>
