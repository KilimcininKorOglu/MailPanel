<?php $pageTitle = 'Create domain'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Create domain</h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domains">Domains</a> /
          <span class="text-light">Create</span>
        </div>
      </div>

      <?php if (!empty($error)): ?>
      <p class="text-error"><?= $e($error) ?></p>
      <?php endif; ?>

      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="domainName">Domain name</label>
          <input id="domainName" type="text" name="domainName" required placeholder="example.com"
            <?php if (!empty($validationErrors['domainName'])): ?>class="error"<?php endif; ?>
            value="<?= $e($domain?->domainName ?? '') ?>"
          />
          <?php if (!empty($validationErrors['domainName'])): ?>
          <span class="text-error"><?= $e($validationErrors['domainName']) ?></span>
          <?php endif; ?>
        </p>

        <p>
          <label for="description">Description</label>
          <input id="description" type="text" name="description"
            value="<?= $e($domain?->description ?? '') ?>"
          />
        </p>

        <div class="row">
          <div class="col-6">
            <p>
              <label for="maxQuota">Max domain quota (MB, 0 = unlimited)</label>
              <input id="maxQuota" type="number" name="maxQuota" min="0"
                value="<?= $e($domain?->maxQuota ?? 0) ?>"
              />
            </p>
          </div>
          <div class="col-6">
            <p>
              <label for="mailboxes">Max mailboxes (0 = unlimited)</label>
              <input id="mailboxes" type="number" name="mailboxes" min="0"
                value="<?= $e($domain?->mailboxes ?? 0) ?>"
              />
            </p>
          </div>
        </div>

        <p>
          <label>
            <input type="checkbox" name="active" <?php if ($domain === null || $domain->active): ?>checked<?php endif; ?> />
            Active
          </label>
        </p>

        <button type="submit" class="button primary">Create domain</button>
        <a href="/domains" class="button outline">Cancel</a>
      </form>
    </div>
  </div>
</div>
