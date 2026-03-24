<?php $pageTitle = 'Mail Log'; ?>
<div class="container">
  <h1>Mail Log</h1>

  <form method="get" style="margin-bottom: 1rem;">
    <div class="row">
      <div class="col-6">
        <input type="text" name="email" placeholder="Filter by email" value="<?= $e($filterEmail ?? '') ?>" />
      </div>
      <div class="col-6">
        <button type="submit" class="button outline">Filter</button>
        <a href="/amavisd/maillog" class="button outline">Clear</a>
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
        <th>Type</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entries as $entry): ?>
      <tr>
        <td><?= $e($entry['time_iso'] ?? '') ?></td>
        <td><?= $e($entry['from_addr'] ?? '') ?></td>
        <td><?= $e($entry['recipient'] ?? '') ?></td>
        <td><?= $e($entry['subject'] ?? '') ?></td>
        <td><?= $e($entry['spam_level'] ?? '') ?></td>
        <td><?= $e($entry['content'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($entries)): ?>
      <tr><td colspan="6" class="text-light">No mail log entries.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (isset($paginatedResult)): ?>
    <?php include __DIR__ . '/pagination.php'; ?>
  <?php endif; ?>
</div>
