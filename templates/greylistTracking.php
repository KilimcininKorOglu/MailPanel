<?php $pageTitle = 'Greylisting Tracking'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1>Greylisting Tracking</h1>
      <p class="text-light">Messages that have passed greylisting verification.</p>

      <table class="striped">
        <thead>
          <tr>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Client IP</th>
            <th>Init Time</th>
            <th>Blocked Count</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entries as $entry): ?>
          <tr>
            <td><?= $e($entry['sender'] ?? '') ?></td>
            <td><?= $e($entry['recipient'] ?? '') ?></td>
            <td><?= $e($entry['client_address'] ?? '') ?></td>
            <td><?= $e($entry['init_time'] ?? '') ?></td>
            <td><?= $e($entry['blocked_count'] ?? 0) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($entries)): ?>
          <tr><td colspan="5" class="text-light">No greylisting tracking data found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <?php if (isset($paginatedResult)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
