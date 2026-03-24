<?php $pageTitle = 'Search'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Search</h1>

      <form method="get" action="/search">
        <div class="row">
          <div class="col-6">
            <input type="text" name="q" value="<?= $e($query) ?>" placeholder="Search domains, users, aliases, mailing lists..." autofocus />
          </div>
          <div class="col-3">
            <select name="accountType[]" multiple>
              <option value="">All types</option>
              <option value="domain" <?= in_array('domain', $accountTypes) ? 'selected' : '' ?>>Domains</option>
              <option value="user" <?= in_array('user', $accountTypes) ? 'selected' : '' ?>>Users</option>
              <option value="alias" <?= in_array('alias', $accountTypes) ? 'selected' : '' ?>>Aliases</option>
              <option value="ml" <?= in_array('ml', $accountTypes) ? 'selected' : '' ?>>Mailing Lists</option>
              <option value="admin" <?= in_array('admin', $accountTypes) ? 'selected' : '' ?>>Admins</option>
            </select>
          </div>
          <div class="col-3">
            <button type="submit" class="button primary">Search</button>
          </div>
        </div>
      </form>

      <?php if ($results !== null): ?>

      <?php
        $totalResults = count($results['domains'] ?? []) + count($results['users'] ?? [])
          + count($results['aliases'] ?? []) + count($results['mailingLists'] ?? [])
          + count($results['admins'] ?? []);
      ?>
      <p class="text-light"><?= $totalResults ?> result(s) found for "<?= $e($query) ?>"</p>

      <?php if (!empty($results['domains'])): ?>
      <h3>Domains (<?= count($results['domains']) ?>)</h3>
      <table class="striped">
        <thead><tr><th>Domain</th><th>Description</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($results['domains'] as $d): ?>
          <tr>
            <td><a href="/domains/<?= $e($d['domain']) ?>/edit"><?= $e($d['domain']) ?></a></td>
            <td><?= $e($d['description'] ?? '') ?></td>
            <td><?= $localize(($d['active'] ?? 1) ? 'active' : 'disabled') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <?php if (!empty($results['users'])): ?>
      <h3>Users (<?= count($results['users']) ?>)</h3>
      <table class="striped">
        <thead><tr><th>Email</th><th>Name</th><th>Domain</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($results['users'] as $u): ?>
          <?php $uid = str_contains($u['username'], '@') ? explode('@', $u['username'])[0] : $u['username']; ?>
          <tr>
            <td><a href="/<?= $e($u['domain']) ?>/users/<?= $e($uid) ?>/general"><?= $e($u['username']) ?></a></td>
            <td><?= $e($u['name'] ?? '') ?></td>
            <td><?= $e($u['domain'] ?? '') ?></td>
            <td><?= $localize(($u['active'] ?? 1) ? 'active' : 'disabled') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <?php if (!empty($results['aliases'])): ?>
      <h3>Aliases (<?= count($results['aliases']) ?>)</h3>
      <table class="striped">
        <thead><tr><th>Address</th><th>Name</th><th>Domain</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($results['aliases'] as $a): ?>
          <tr>
            <td><a href="/aliases/<?= $e($a['address']) ?>"><?= $e($a['address']) ?></a></td>
            <td><?= $e($a['name'] ?? '') ?></td>
            <td><?= $e($a['domain'] ?? '') ?></td>
            <td><?= $localize(($a['active'] ?? 1) ? 'active' : 'disabled') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <?php if (!empty($results['mailingLists'])): ?>
      <h3>Mailing Lists (<?= count($results['mailingLists']) ?>)</h3>
      <table class="striped">
        <thead><tr><th>Address</th><th>Name</th><th>Domain</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($results['mailingLists'] as $ml): ?>
          <tr>
            <td><a href="/mailing-lists/<?= $e($ml['address']) ?>"><?= $e($ml['address']) ?></a></td>
            <td><?= $e($ml['name'] ?? '') ?></td>
            <td><?= $e($ml['domain'] ?? '') ?></td>
            <td><?= $localize(($ml['active'] ?? 1) ? 'active' : 'disabled') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <?php if (!empty($results['admins'])): ?>
      <h3>Admins (<?= count($results['admins']) ?>)</h3>
      <table class="striped">
        <thead><tr><th>Email</th><th>Name</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($results['admins'] as $adm): ?>
          <tr>
            <td><a href="/admins/<?= $e($adm['username']) ?>/general"><?= $e($adm['username']) ?></a></td>
            <td><?= $e($adm['name'] ?? '') ?></td>
            <td><?= $localize(($adm['active'] ?? 1) ? 'active' : 'disabled') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <?php if ($totalResults === 0): ?>
      <p class="text-light">No results found.</p>
      <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</div>
