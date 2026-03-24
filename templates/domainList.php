<?php $pageTitle = 'Domains'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Domains</h1>
      <table class="striped">
        <thead>
          <tr>
            <th>Name</th>
            <th>User count</th>
            <th>Domain active</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($domainInfo as $info): ?>
          <tr>
            <td>
              <a href="/<?= $e($info['domainName']) ?>/users"><?= $e($info['domainName']) ?></a>
            </td>
            <td><?= $e($info['domainCurrentUserNumber'] ?? '') ?></td>
            <td><?= $localize($info['accountStatus'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
