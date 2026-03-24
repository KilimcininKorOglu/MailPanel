<?php $pageTitle = 'Fail2ban'; ?>
<div class="container">
  <h1>Fail2ban Status</h1>

  <?php foreach ($jails as $jail): ?>
  <div class="card" style="margin-bottom: 1rem;">
    <header><h3><?= $e($jail) ?></h3></header>

    <?php $ips = $bannedIps[$jail] ?? []; ?>
    <?php if (!empty($ips)): ?>
    <table class="striped">
      <thead>
        <tr>
          <th>Banned IP</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ips as $ip): ?>
        <tr>
          <td><?= $e($ip) ?></td>
          <td>
            <form method="post" action="/fail2ban/unban" style="display:inline" onsubmit="return confirm('Unban <?= $e($ip) ?>?')">
              <?= $csrfField ?>
              <input type="hidden" name="jail" value="<?= $e($jail) ?>" />
              <input type="hidden" name="ip" value="<?= $e($ip) ?>" />
              <button type="submit" class="button outline">Unban</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p class="text-light">No banned IPs in this jail.</p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="card">
    <header><h3>Ban an IP</h3></header>
    <form method="post" action="/fail2ban/ban">
      <?= $csrfField ?>
      <div class="row">
        <div class="col-4">
          <select name="jail" required>
            <?php foreach ($jails as $jail): ?>
            <option value="<?= $e($jail) ?>"><?= $e($jail) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-4">
          <input type="text" name="ip" placeholder="IP address" required pattern="[0-9a-fA-F.:]*" />
        </div>
        <div class="col-4">
          <button type="submit" class="button error outline">Ban IP</button>
        </div>
      </div>
    </form>
  </div>
</div>
