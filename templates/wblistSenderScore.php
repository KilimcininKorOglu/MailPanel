<?php $pageTitle = 'SenderScore Whitelist'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>SenderScore Whitelist</h1>
      <p class="text-light">Permanently whitelist IP addresses to bypass SenderScore checks.</p>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= $csrfField ?>

        <fieldset>
          <legend>Whitelisted IP Addresses</legend>
          <textarea name="ips" rows="10" placeholder="192.168.1.1&#10;10.0.0.1"><?= $e(implode("\n", $ips)) ?></textarea>
          <p class="text-light">One IP address per line (IPv4 or IPv6). These IPs will permanently bypass SenderScore checks.</p>
        </fieldset>

        <button type="submit" class="button primary">Save</button>
      </form>
    </div>
  </div>
</div>
