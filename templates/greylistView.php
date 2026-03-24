<?php $pageTitle = 'Greylisting: ' . $e($account); ?>
<div class="container">
  <h1>Greylisting Settings</h1>

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

  <h3>Greylisting status</h3>
  <form method="post">
    <?= $csrfField ?>
    <input type="hidden" name="action" value="toggle" />
    <p>
      <label>
        <input type="checkbox" name="enabled" <?php if ($greylistEnabled): ?>checked<?php endif; ?> />
        Greylisting enabled for <?= $e($account) ?>
      </label>
    </p>
    <p>
      <button type="submit" class="button primary">Save</button>
    </p>
  </form>

  <h3>Whitelisted senders</h3>
  <form method="post">
    <?= $csrfField ?>
    <input type="hidden" name="action" value="whitelist" />
    <p>
      <label for="whitelistedSenders">Senders (one per line, e.g., @example.com or user@example.com)</label>
      <textarea id="whitelistedSenders" name="whitelistedSenders" rows="6"><?= $e(implode("\n", $whitelistedSenders ?? [])) ?></textarea>
    </p>
    <p>
      <button type="submit" class="button primary">Save whitelist</button>
    </p>
  </form>
</div>
