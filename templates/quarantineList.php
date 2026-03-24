<?php $pageTitle = 'Quarantine'; ?>
<div class="container">
  <h1>Quarantined Messages</h1>

  <form method="get" style="margin-bottom: 1rem;">
    <div class="row">
      <div class="col-6">
        <input type="text" name="domain" placeholder="Filter by domain" value="<?= $e($filterDomain ?? '') ?>" />
      </div>
      <div class="col-6">
        <button type="submit" class="button outline">Filter</button>
        <a href="/amavisd/quarantine" class="button outline">Clear</a>
        <form method="post" action="/amavisd/cleanup" style="display:inline" onsubmit="return confirm('Clean up old quarantined messages and mail logs?')">
          <?= $csrfField ?>
          <button type="submit" class="button error outline">Cleanup old records</button>
        </form>
      </div>
    </div>
  </form>

  <table class="striped">
    <thead>
      <tr>
        <th>Date</th>
        <th>From</th>
        <th>To</th>
        <th>Subject</th>
        <th>Spam level</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($messages as $msg): ?>
      <tr>
        <td><?= $e($msg['time_iso'] ?? '') ?></td>
        <td><?= $e($msg['from_addr'] ?? '') ?></td>
        <td><?= $e($msg['recipient'] ?? '') ?></td>
        <td><?= $e($msg['subject'] ?? '') ?></td>
        <td><?= $e($msg['spam_level'] ?? '') ?></td>
        <td>
          <form method="post" action="/amavisd/quarantine/<?= $e($msg['mail_id'] ?? '') ?>/release" style="display:inline" onsubmit="return confirm('Release this message to the recipient?')">
            <?= $csrfField ?>
            <button type="submit" class="button primary outline">Release</button>
          </form>
          <form method="post" action="/amavisd/quarantine/<?= $e($msg['mail_id'] ?? '') ?>/delete" style="display:inline" onsubmit="return confirm('Delete this quarantined message?')">
            <?= $csrfField ?>
            <button type="submit" class="button error outline">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
      <tr><td colspan="6" class="text-light">No quarantined messages.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (isset($paginatedResult)): ?>
    <?php include __DIR__ . '/pagination.php'; ?>
  <?php endif; ?>
</div>
