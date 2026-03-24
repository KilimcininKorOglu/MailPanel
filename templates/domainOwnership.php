<?php $pageTitle = 'Domain Ownership Verification'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Domain Ownership Verification</h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($pendingDomains)): ?>
      <table class="striped">
        <thead>
          <tr>
            <th>Domain</th>
            <th>Verification Code</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingDomains as $pd): ?>
          <tr>
            <td><?= $e($pd['domain']) ?></td>
            <td><code><?= $e($pd['verify_code']) ?></code></td>
            <td><?= (int) ($pd['verified'] ?? 0) ? 'Verified' : 'Pending' ?></td>
            <td>
              <?php if (!(int) ($pd['verified'] ?? 0)): ?>
              <form method="post" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="verify" />
                <input type="hidden" name="domain" value="<?= $e($pd['domain']) ?>" />
                <button type="submit" class="button primary outline">Verify DNS</button>
              </form>
              <?php if (!empty($session['isGlobalAdmin'])): ?>
              <form method="post" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="force_verify" />
                <input type="hidden" name="domain" value="<?= $e($pd['domain']) ?>" />
                <button type="submit" class="button outline">Force Verify</button>
              </form>
              <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <p class="text-light">Add a TXT record to your domain's DNS with the verification code shown above.</p>
      <?php else: ?>
      <p class="text-light">No pending domain verifications.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
