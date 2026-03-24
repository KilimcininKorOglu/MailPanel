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

      <div class="row">
        <div class="col">
          <a href="/<?= $e($domain) ?>/users/create" class="button primary outline">Create</a>
        </div>
      </div>

      <table class="striped">
        <thead>
          <tr>
            <th>Identifier</th>
            <th>Quota</th>
            <th>Global administrator</th>
            <th>Account active</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td>
              <a href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/general"><?= $e($user->uid) ?></a>
            </td>
            <td><?= $e($user->mailQuota) ?></td>
            <td><?= $localize($user->domainGlobalAdmin) ?></td>
            <td><?= $localize($user->accountStatus) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
