<?php $pageTitle = 'Last Login Tracking'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Last Login Tracking</h1>

      <form method="get" action="/last-logins" style="margin-bottom:1rem;">
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

      <table class="striped">
        <thead>
          <tr>
            <th>Username</th>
            <th>Domain</th>
            <th>IMAP</th>
            <th>POP3</th>
            <th>LDA</th>
            <th>LMTP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logins as $login): ?>
          <tr>
            <td><?= $e($login['username']) ?></td>
            <td><?= $e($login['domain']) ?></td>
            <td><?= $e($login['imap'] ?? 'Never') ?></td>
            <td><?= $e($login['pop3'] ?? 'Never') ?></td>
            <td><?= $e($login['lda'] ?? 'Never') ?></td>
            <td><?= $e($login['lmtp'] ?? 'Never') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($logins)): ?>
          <tr><td colspan="6" class="text-light">No login data available. Dovecot last_login plugin may not be enabled.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>

      <p><a href="/system-settings">&larr; Back to system settings</a></p>
    </div>
  </div>
</div>
