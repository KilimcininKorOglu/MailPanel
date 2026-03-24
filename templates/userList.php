<?php $pageTitle = 'Users'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Users</h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domains"><?= $e($domain) ?></a> /
          <span class="text-light">Users</span>
        </div>
      </div>

      <?php if (!empty($supportsCreate)): ?>
      <div class="row">
        <div class="col">
          <a href="/<?= $e($domain) ?>/users/create" class="button primary outline">Create user</a>
        </div>
      </div>
      <?php endif; ?>

      <?php
      $letters = range('A', 'Z');
      $baseUrl = '/' . urlencode($domain) . '/users';
      ?>
      <div style="margin: 0.5rem 0;">
        <a href="<?= $e($baseUrl) ?>" <?php if (empty($currentLetter)): ?>style="font-weight:bold"<?php endif; ?>>All</a>
        <?php foreach ($letters as $letter): ?>
          <a href="<?= $e($baseUrl) ?>?letter=<?= $e($letter) ?>"
             <?php if (($currentLetter ?? '') === $letter): ?>style="font-weight:bold"<?php endif; ?>><?= $letter ?></a>
        <?php endforeach; ?>
      </div>

      <table class="striped">
        <thead>
          <tr>
            <?php
            $sortUrl = function(string $col) use ($baseUrl, $sortBy, $sortDir, $currentLetter) {
                $newDir = ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc';
                $params = ['sort' => $col, 'dir' => $newDir];
                if ($currentLetter) $params['letter'] = $currentLetter;
                return $baseUrl . '?' . http_build_query($params);
            };
            $sortIcon = function(string $col) use ($sortBy, $sortDir) {
                if ($sortBy !== $col) return '';
                return $sortDir === 'asc' ? ' &#9650;' : ' &#9660;';
            };
            ?>
            <th><a href="<?= $e($sortUrl('uid')) ?>">Identifier<?= $sortIcon('uid') ?></a></th>
            <th><a href="<?= $e($sortUrl('mailQuota')) ?>">Quota (MB)<?= $sortIcon('mailQuota') ?></a></th>
            <th>Global admin</th>
            <th><a href="<?= $e($sortUrl('accountStatus')) ?>">Status<?= $sortIcon('accountStatus') ?></a></th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td>
              <a href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/general"><?= $e($user->uid) ?></a>
            </td>
            <td><?= $e($user->mailQuota === 0 ? 'Unlimited' : $user->mailQuota) ?></td>
            <td><?= $localize($user->domainGlobalAdmin) ?></td>
            <td><?= $localize($user->accountStatus) ?></td>
            <td>
              <a href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/general" class="button primary outline">Edit</a>
              <form method="post" action="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/delete" style="display:inline" onsubmit="return confirm('Delete user <?= $e($user->uid) ?>?')">
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
