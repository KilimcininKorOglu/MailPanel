<?php $pageTitle = $e($domain->domainName); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1><?= $e($domain->domainName) ?></h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domains">Domains</a> /
          <span class="text-light"><?= $e($domain->domainName) ?></span>
        </div>
      </div>

      <?php if (!empty($error)): ?>
      <p class="text-error"><?= $e($error) ?></p>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
      <p class="text-success"><?= $e($success) ?></p>
      <?php endif; ?>

      <p class="text-light">
        Users: <?= $e($domain->currentUserCount) ?> |
        Created: <?= $e($domain->created ?? 'N/A') ?>
      </p>

      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="description">Description</label>
          <input id="description" type="text" name="description"
            value="<?= $e($domain->description) ?>"
          />
        </p>

        <div class="row">
          <div class="col-6">
            <p>
              <label for="maxQuota">Max domain quota (MB, 0 = unlimited)</label>
              <input id="maxQuota" type="number" name="maxQuota" min="0"
                value="<?= $e($domain->maxQuota) ?>"
              />
            </p>
          </div>
          <div class="col-6">
            <p>
              <label for="quota">Domain quota (MB, 0 = unlimited)</label>
              <input id="quota" type="number" name="quota" min="0"
                value="<?= $e($domain->quota) ?>"
              />
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
            <p>
              <label for="mailboxes">Max mailboxes (0 = unlimited)</label>
              <input id="mailboxes" type="number" name="mailboxes" min="0"
                value="<?= $e($domain->mailboxes) ?>"
              />
            </p>
          </div>
          <div class="col-6">
            <p>
              <label for="aliases">Max aliases (0 = unlimited)</label>
              <input id="aliases" type="number" name="aliases" min="0"
                value="<?= $e($domain->aliases) ?>"
              />
            </p>
          </div>
        </div>

        <p>
          <label for="transport">Transport</label>
          <select id="transport" name="transport">
            <option value="dovecot" <?php if ($domain->transport === 'dovecot'): ?>selected<?php endif; ?>>dovecot</option>
            <option value="lmtp" <?php if ($domain->transport === 'lmtp'): ?>selected<?php endif; ?>>lmtp</option>
          </select>
        </p>

        <p>
          <label>
            <input type="checkbox" name="active" <?php if ($domain->active): ?>checked<?php endif; ?> />
            Active
          </label>
        </p>

        <button type="submit" class="button primary">Save changes</button>
        <a href="/domains" class="button outline">Back to domains</a>
      </form>
    </div>
  </div>
</div>
