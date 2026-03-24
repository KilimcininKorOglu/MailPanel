<?php $pageTitle = 'Mailing Lists'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Mailing Lists</h1>

      <div class="row">
        <div class="col">
          <?php if (!empty($session['isGlobalAdmin'])): ?>
          <a href="/mailing-lists/create" class="button primary outline">Create mailing list</a>
          <?php endif; ?>

          <form method="get" action="/mailing-lists" style="display:inline-block; margin-left:1rem;">
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

      <form method="post" action="/mailing-lists/bulk">
        <?= $csrfField ?>
        <table class="striped">
          <thead>
            <tr>
              <th><input type="checkbox" onclick="document.querySelectorAll('input[name=\'selected[]\']').forEach(c=>c.checked=this.checked)" /></th>
              <th>Address</th>
              <th>Name</th>
              <th>Domain</th>
              <th>Policy</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($mailingLists as $ml): ?>
            <tr>
              <td><input type="checkbox" name="selected[]" value="<?= $e($ml->address) ?>" /></td>
              <td><a href="/mailing-lists/<?= $e($ml->address) ?>"><?= $e($ml->address) ?></a></td>
              <td><?= $e($ml->name) ?></td>
              <td><a href="/<?= $e($ml->domain) ?>/users"><?= $e($ml->domain) ?></a></td>
              <td><?= $e($ml->accessPolicy) ?></td>
              <td><?= $localize($ml->active ? 'active' : 'disabled') ?></td>
              <td>
                <form method="post" action="/mailing-lists/<?= $e($ml->address) ?>/delete" style="display:inline" onsubmit="return confirm('Delete mailing list <?= $e($ml->address) ?>?')">
                  <?= $csrfField ?>
                  <button type="submit" class="button error outline">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($mailingLists)): ?>
            <tr><td colspan="7" class="text-light">No mailing lists found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if (!empty($mailingLists) && !empty($session['isGlobalAdmin'])): ?>
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
