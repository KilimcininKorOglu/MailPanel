<?php $pageTitle = 'rDNS White/Blacklist'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>rDNS White/Blacklist</h1>
      <p class="text-light">Manage reverse DNS based white/blacklist for iRedAPD.</p>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= $csrfField ?>

        <fieldset>
          <legend>Whitelisted rDNS Domains</legend>
          <textarea name="whitelists" rows="8" placeholder="example.com&#10;trusted-relay.org"><?= $e(implode("\n", $whitelists)) ?></textarea>
          <p class="text-light">One domain per line. Emails from servers with matching rDNS will bypass greylisting.</p>
        </fieldset>

        <fieldset>
          <legend>Blacklisted rDNS Domains</legend>
          <textarea name="blacklists" rows="8" placeholder="spam-source.com"><?= $e(implode("\n", $blacklists)) ?></textarea>
          <p class="text-light">One domain per line. Emails from servers with matching rDNS will be rejected.</p>
        </fieldset>

        <button type="submit" class="button primary">Save</button>
      </form>
    </div>
  </div>
</div>
