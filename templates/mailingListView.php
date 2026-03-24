<?php $pageTitle = 'Mailing List: ' . ($ml->address ?? ''); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Mailing List: <?= $e($ml->address) ?></h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="updateSettings" />

        <fieldset>
          <legend>List Settings</legend>

          <label for="name">Display Name</label>
          <input type="text" id="name" name="name" value="<?= $e($ml->name) ?>" />

          <label for="accessPolicy">Access Policy</label>
          <select id="accessPolicy" name="accessPolicy">
            <option value="public" <?= $ml->accessPolicy === 'public' ? 'selected' : '' ?>>Public</option>
            <option value="domain" <?= $ml->accessPolicy === 'domain' ? 'selected' : '' ?>>Domain</option>
            <option value="membersOnly" <?= $ml->accessPolicy === 'membersOnly' ? 'selected' : '' ?>>Members Only</option>
            <option value="moderatorsOnly" <?= $ml->accessPolicy === 'moderatorsOnly' ? 'selected' : '' ?>>Moderators Only</option>
          </select>

          <div class="row">
            <div class="col-6">
              <label for="maxMsgSize">Max message size (bytes, 0 = unlimited)</label>
              <input type="number" id="maxMsgSize" name="maxMsgSize" min="0" value="<?= $e($ml->maxMsgSize) ?>" />
            </div>
            <div class="col-6">
              <label for="maxMembers">Max members (0 = unlimited)</label>
              <input type="number" id="maxMembers" name="maxMembers" min="0" value="<?= $e($ml->maxMembers) ?>" />
            </div>
          </div>

          <label>
            <input type="checkbox" name="active" <?= $ml->active ? 'checked' : '' ?> />
            Active
          </label>

          <p class="text-light">Transport: <?= $e($ml->transport) ?></p>
        </fieldset>

        <button type="submit" class="button primary">Save Settings</button>
      </form>

      <hr />

      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="updateOwners" />

        <fieldset>
          <legend>List Owners</legend>
          <label for="owners">Owner email addresses (one per line)</label>
          <textarea id="owners" name="owners" rows="4"><?= $e(implode("\n", $owners)) ?></textarea>
          <p class="text-light">Owners can manage list settings and moderate messages.</p>
        </fieldset>

        <button type="submit" class="button primary outline">Save Owners</button>
      </form>

      <hr />

      <?php if (!empty($session['isGlobalAdmin'])): ?>
      <form method="post" action="/mailing-lists/<?= $e($ml->address) ?>/delete" onsubmit="return confirm('Delete mailing list <?= $e($ml->address) ?>?')">
        <?= $csrfField ?>
        <button type="submit" class="button error">Delete Mailing List</button>
      </form>
      <?php endif; ?>

      <p><a href="/mailing-lists">&larr; Back to mailing lists</a></p>
    </div>
  </div>
</div>
