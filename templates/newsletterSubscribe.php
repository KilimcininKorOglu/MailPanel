<?php $pageTitle = ($action === 'subscribe' ? 'Subscribe to' : 'Unsubscribe from') . ' ' . ($ml->name ?: $ml->address); ?>
<div class="container">
  <div class="row">
    <div class="col-6">
      <h1><?= $e($pageTitle) ?></h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <?php if (empty($success)): ?>
      <p><?= $action === 'subscribe' ? 'Enter your email to subscribe to this mailing list.' : 'Enter your email to unsubscribe from this mailing list.' ?></p>

      <form method="post">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required placeholder="your@email.com" />

        <button type="submit" class="button primary"><?= $action === 'subscribe' ? 'Subscribe' : 'Unsubscribe' ?></button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
