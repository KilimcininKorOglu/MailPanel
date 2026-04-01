<?php $pageTitle = 'Mail Aliases'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Mail Aliases</h1>

      <div class="row">
        <div class="col">
          <?php if (!empty($session['isGlobalAdmin'])): ?>
          <a href="/aliases/create" class="button primary outline">Create mail alias</a>
          <?php endif; ?>

          <form method="get" action="/aliases" style="display:inline-block; margin-left:1rem;">
            <select name="domain" onchange="this.form.submit()">
              <option value="">All domains</option>
              <?php foreach ($domains as $d): ?>
              <option value="<?= $e($d['domain'] ?? $d['name'] ?? '') ?>"
                <?= ($filterDomain === ($d['domain'] ?? $d['name'] ?? '')) ? 'selected' : '' ?>>
                <?= $e($d['domain'] ?? $d['name'] ?? '') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>

      <form method="post" action="/aliases/bulk">
        <?= $csrfField ?>
        <table class="striped">
          <thead>
            <tr>
              <th><input type="checkbox" onclick="document.querySelectorAll('input[name=\'selected[]\']').forEach(c=>c.checked=this.checked)" /></th>
              <th>Address</th>
              <th>Name</th>
              <th>Domain</th>
              <th>Access Policy</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aliases as $alias): ?>
            <tr>
              <td><input type="checkbox" name="selected[]" value="<?= $e($alias->address) ?>" /></td>
              <td><a href="/aliases/<?= $e($alias->address) ?>"><?= $e($alias->address) ?></a></td>
              <td><?= $e($alias->name) ?></td>
              <td><a href="/<?= $e($alias->domain) ?>/users"><?= $e($alias->domain) ?></a></td>
              <td><?= $e($alias->accessPolicy) ?></td>
              <td><?= $localize($alias->active ? 'active' : 'disabled') ?></td>
              <td><?= $e($alias->created ?? '') ?></td>
              <td>
                <form method="post" action="/aliases/<?= $e($alias->address) ?>/delete" style="display:inline" data-confirm="Delete alias <?= $e($alias->address) ?>?">
                  <?= $csrfField ?>
                  <button type="submit" class="button error outline">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($aliases)): ?>
            <tr><td colspan="8" class="text-light">No mail aliases found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if (!empty($aliases) && !empty($session['isGlobalAdmin'])): ?>
        <div class="row" style="margin-top:1rem;">
          <div class="col">
            <select name="action">
              <option value="">-- Bulk action --</option>
              <option value="enable">Enable</option>
              <option value="disable">Disable</option>
              <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button outline" onclick="return this.form.action.value && confirm('Apply bulk action?')">Apply</button>
          </div>
        </div>
        <?php endif; ?>
      </form>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
