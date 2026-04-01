<?php $pageTitle = 'Deleted Mailboxes'; ?>
<div class="container">
  <h1>Deleted Mailboxes</h1>

  <table class="striped">
    <thead>
      <tr>
        <th>Username</th>
        <th>Domain</th>
        <th>Maildir</th>
        <th>Deleted by</th>
        <th>Scheduled deletion</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($deletedMailboxes as $mb): ?>
      <tr>
        <td><?= $e($mb->username) ?></td>
        <td><?= $e($mb->domain) ?></td>
        <td style="font-size:0.85em; word-break:break-all;"><?= $e($mb->maildir) ?></td>
        <td><?= $e($mb->admin) ?></td>
        <td><?= $e($mb->deleteDate ?? 'Not scheduled') ?></td>
        <td><?= $e($mb->timestamp ?? '') ?></td>
        <td>
          <form method="post" action="/deleted-mailboxes/<?= $e($mb->id) ?>/cancel" style="display:inline" data-confirm="Cancel deletion? The mailbox directory will be preserved.">
            <?= $csrfField ?>
            <button type="submit" class="button outline">Cancel</button>
          </form>
          <form method="post" action="/deleted-mailboxes/<?= $e($mb->id) ?>/reschedule" style="display:inline">
            <?= $csrfField ?>
            <input type="date" name="newDate" required style="width:auto; display:inline-block;" />
            <button type="submit" class="button outline">Reschedule</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($deletedMailboxes)): ?>
      <tr><td colspan="7" class="text-light">No pending mailbox deletions.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (isset($paginatedResult)): ?>
    <?php include __DIR__ . '/pagination.php'; ?>
  <?php endif; ?>
</div>
