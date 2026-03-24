<?php $pageTitle = 'Create Mailing List'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Create Mailing List</h1>

      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="/mailing-lists/create">
        <?= $csrfField ?>

        <div class="row">
          <div class="col-6">
            <label for="localPart">Local Part</label>
            <input type="text" id="localPart" name="localPart" required value="<?= $e($_POST['localPart'] ?? '') ?>" placeholder="list-name" />
          </div>
          <div class="col-6">
            <label for="domain">Domain</label>
            <select id="domain" name="domain" required>
              <?php foreach ($domains as $d): ?>
              <option value="<?= $e($d['domain'] ?? $d['name'] ?? '') ?>">@<?= $e($d['domain'] ?? $d['name'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <label for="name">Display Name</label>
        <input type="text" id="name" name="name" value="<?= $e($_POST['name'] ?? '') ?>" placeholder="e.g. Project Discussion" />

        <label for="accessPolicy">Access Policy</label>
        <select id="accessPolicy" name="accessPolicy">
          <option value="public">Public (anyone can send)</option>
          <option value="domain">Domain (only same domain)</option>
          <option value="membersOnly">Members Only</option>
          <option value="moderatorsOnly">Moderators Only</option>
        </select>

        <div class="row">
          <div class="col-6">
            <label for="maxMsgSize">Max message size (bytes, 0 = unlimited)</label>
            <input type="number" id="maxMsgSize" name="maxMsgSize" min="0" value="0" />
          </div>
          <div class="col-6">
            <label for="maxMembers">Max members (0 = unlimited)</label>
            <input type="number" id="maxMembers" name="maxMembers" min="0" value="0" />
          </div>
        </div>

        <button type="submit" class="button primary">Create Mailing List</button>
        <a href="/mailing-lists" class="button outline">Cancel</a>
      </form>
    </div>
  </div>
</div>
