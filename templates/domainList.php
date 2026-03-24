<?php $pageTitle = 'Domains'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Domains</h1>

      <div class="row">
        <div class="col">
          <a href="/domains/create" class="button primary outline">Create domain</a>
        </div>
      </div>

      <div style="margin: 0.5rem 0;">
        <a href="/domains" <?php if (empty($statusFilter)): ?>style="font-weight:bold"<?php endif; ?>>All</a> |
        <a href="/domains?status=active" <?php if (($statusFilter ?? '') === 'active'): ?>style="font-weight:bold"<?php endif; ?>>Active</a> |
        <a href="/domains?status=disabled" <?php if (($statusFilter ?? '') === 'disabled'): ?>style="font-weight:bold"<?php endif; ?>>Disabled</a>
      </div>

      <form method="post" action="/domains/bulk">
        <?= $csrfField ?>
      <table class="striped">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll" onclick="document.querySelectorAll('input[name=\\'selectedDomains[]\\']').forEach(c=>c.checked=this.checked)" /></th>
            <th>Name</th>
            <th>Description</th>
            <th>Users</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($domains as $domain): ?>
          <tr>
            <td><input type="checkbox" name="selectedDomains[]" value="<?= $e($domain->domainName) ?>" /></td>
            <td>
              <a href="/<?= $e($domain->domainName) ?>/users"><?= $e($domain->domainName) ?></a>
            </td>
            <td><?= $e($domain->description) ?></td>
            <td><?= $e($domain->currentUserCount) ?></td>
            <td><?= $localize($domain->active ? 'active' : 'disabled') ?></td>
            <td>
              <a href="/domains/<?= $e($domain->domainName) ?>/edit" class="button primary outline">Edit</a>
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
        <button type="submit" class="button outline" onclick="return this.form.action.value==='delete' ? confirm('Delete selected domains and all their users?') : true">Apply</button>
      </div>
      </form>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
