<?php $pageTitle = !empty($showConfirmButton) ? 'Confirm Action' : 'Confirmed'; ?>
<div class="container">
  <div class="row">
    <div class="col-6">
      <h1><?= $e($pageTitle) ?></h1>
      <p><?= $e($message) ?></p>
      <?php if (!empty($showConfirmButton)): ?>
      <form method="POST">
        <button type="submit" class="button primary">Confirm</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
