<?php $pageTitle = 'Throttle: ' . $e($account); ?>
<div class="container">
  <h1>Throttle Settings</h1>

  <div class="row breadcrumbs">
    <div class="col">
      <span class="text-light"><?= $e($account) ?></span>
    </div>
  </div>

  <?php if (!empty($error)): ?>
  <p class="text-error"><?= $e($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
  <p class="text-success"><?= $e($success) ?></p>
  <?php endif; ?>

  <?php if (!empty($throttleSettings)): ?>
  <h3>Current settings</h3>
  <table class="striped">
    <thead>
      <tr>
        <th>Kind</th>
        <th>Period (sec)</th>
        <th>Max messages</th>
        <th>Max quota (bytes)</th>
        <th>Max message size</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($throttleSettings as $t): ?>
      <tr>
        <td><?= $e($t['kind'] ?? '') ?></td>
        <td><?= $e($t['period'] ?? '') ?></td>
        <td><?= $e($t['max_msgs'] ?? 0) ?></td>
        <td><?= $e($t['max_quota'] ?? 0) ?></td>
        <td><?= $e($t['msg_size'] ?? 0) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p class="text-light">No throttle settings configured for this account.</p>
  <?php endif; ?>

  <h3>Set throttle</h3>
  <form method="post">
    <?= $csrfField ?>
    <div class="row">
      <div class="col-3">
        <label>Kind</label>
        <select name="kind">
          <option value="outbound">Outbound</option>
          <option value="inbound">Inbound</option>
        </select>
      </div>
      <div class="col-3">
        <label>Period (seconds)</label>
        <input type="number" name="period" value="3600" min="0" />
      </div>
      <div class="col-3">
        <label>Max messages</label>
        <input type="number" name="maxMsgs" value="0" min="0" />
      </div>
      <div class="col-3">
        <label>Max quota (bytes)</label>
        <input type="number" name="maxQuota" value="0" min="0" />
      </div>
    </div>
    <p>
      <button type="submit" class="button primary">Save throttle</button>
    </p>
  </form>
</div>
