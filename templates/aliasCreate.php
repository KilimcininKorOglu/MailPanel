<?php $pageTitle = 'Create Mail Alias'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Create Mail Alias</h1>

      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="/aliases/create">
        <?= $csrfField ?>

        <fieldset>
          <legend>Alias Address</legend>

          <div class="row">
            <div class="col-6">
              <label for="localPart">Local Part</label>
              <input type="text" id="localPart" name="localPart" required value="<?= $e($_POST['localPart'] ?? '') ?>" placeholder="alias-name" />
            </div>
            <div class="col-6">
              <label for="domain">Domain</label>
              <select id="domain" name="domain" required>
                <?php foreach ($domains as $d): ?>
                <option value="<?= $e($d['domain'] ?? $d['name'] ?? '') ?>"
                  <?= (($_POST['domain'] ?? '') === ($d['domain'] ?? $d['name'] ?? '')) ? 'selected' : '' ?>>
                  @<?= $e($d['domain'] ?? $d['name'] ?? '') ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <label for="name">Display Name</label>
          <input type="text" id="name" name="name" value="<?= $e($_POST['name'] ?? '') ?>" placeholder="e.g. Sales Team" />

          <label for="accessPolicy">Access Policy</label>
          <select id="accessPolicy" name="accessPolicy">
            <option value="public" <?= (($_POST['accessPolicy'] ?? 'public') === 'public') ? 'selected' : '' ?>>Public (anyone can send)</option>
            <option value="domain" <?= (($_POST['accessPolicy'] ?? '') === 'domain') ? 'selected' : '' ?>>Domain (only same domain)</option>
            <option value="membersOnly" <?= (($_POST['accessPolicy'] ?? '') === 'membersOnly') ? 'selected' : '' ?>>Members Only</option>
            <option value="moderatorsOnly" <?= (($_POST['accessPolicy'] ?? '') === 'moderatorsOnly') ? 'selected' : '' ?>>Moderators Only</option>
          </select>
        </fieldset>

        <fieldset>
          <legend>Members</legend>
          <label for="members">Member email addresses (one per line)</label>
          <textarea id="members" name="members" rows="6" placeholder="user1@example.com&#10;user2@example.com"><?= $e($_POST['members'] ?? '') ?></textarea>
        </fieldset>

        <button type="submit" class="button primary">Create Alias</button>
        <a href="/aliases" class="button outline">Cancel</a>
      </form>
    </div>
  </div>
</div>
