<?php $pageTitle = 'Domain Aliases'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Domain Aliases</h1>

      <div class="row">
        <div class="col">
          <a href="/domain-aliases/create" class="button primary outline">Create domain alias</a>
        </div>
      </div>

      <table class="striped">
        <thead>
          <tr>
            <th>Alias Domain</th>
            <th>Target Domain</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($aliases as $alias): ?>
          <tr>
            <td><?= $e($alias->aliasDomain) ?></td>
            <td><a href="/<?= $e($alias->targetDomain) ?>/users"><?= $e($alias->targetDomain) ?></a></td>
            <td><?= $localize($alias->active ? 'active' : 'disabled') ?></td>
            <td><?= $e($alias->created ?? '') ?></td>
            <td>
              <form method="post" action="/domain-aliases/<?= $e($alias->aliasDomain) ?>/delete" style="display:inline" data-confirm="Delete alias <?= $e($alias->aliasDomain) ?>?">
                <?= $csrfField ?>
                <button type="submit" class="button error outline">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($aliases)): ?>
          <tr><td colspan="5" class="text-light">No domain aliases found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
