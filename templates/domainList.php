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

      <table class="striped">
        <thead>
          <tr>
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
            <td>
              <a href="/<?= $e($domain->domainName) ?>/users"><?= $e($domain->domainName) ?></a>
            </td>
            <td><?= $e($domain->description) ?></td>
            <td><?= $e($domain->currentUserCount) ?></td>
            <td><?= $localize($domain->active ? 'active' : 'disabled') ?></td>
            <td>
              <a href="/domains/<?= $e($domain->domainName) ?>/edit" class="button primary outline">Edit</a>
              <form method="post" action="/domains/<?= $e($domain->domainName) ?>/delete" style="display:inline" onsubmit="return confirm('Delete domain <?= $e($domain->domainName) ?> and all its users?')">
                <?= $csrfField ?>
                <button type="submit" class="button error outline">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
