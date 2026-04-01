<?php $pageTitle = 'Spam Policy: ' . ($account ?? ''); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Spam Policy</h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <p>
        <strong>Account:</strong> <?= $e($account) ?>
        <?php if ($account === '@.'): ?>(Global default)<?php endif; ?>
      </p>

      <form method="get" action="/amavisd/spam-policy" style="margin-bottom:1rem;">
        <div class="row">
          <div class="col-8">
            <input type="text" name="account" value="<?= $e($account !== '@.' ? $account : '') ?>" placeholder="@. (global), @domain.com, or user@domain.com" />
          </div>
          <div class="col-4">
            <button type="submit" class="button outline">Load Policy</button>
          </div>
        </div>
      </form>

      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="save" />

        <fieldset>
          <legend>Spam Thresholds</legend>

          <div class="row">
            <div class="col-4">
              <label for="spamTagLevel">Tag Level</label>
              <input id="spamTagLevel" type="number" step="0.1" name="spamTagLevel"
                value="<?= $e($policy->spamTagLevel ?? '') ?>" placeholder="e.g. 2.0" />
              <p class="text-light">Add spam header</p>
            </div>
            <div class="col-4">
              <label for="spamTag2Level">Tag2 Level</label>
              <input id="spamTag2Level" type="number" step="0.1" name="spamTag2Level"
                value="<?= $e($policy->spamTag2Level ?? '') ?>" placeholder="e.g. 6.2" />
              <p class="text-light">Mark as spam</p>
            </div>
            <div class="col-4">
              <label for="spamKillLevel">Kill Level</label>
              <input id="spamKillLevel" type="number" step="0.1" name="spamKillLevel"
                value="<?= $e($policy->spamKillLevel ?? '') ?>" placeholder="e.g. 6.9" />
              <p class="text-light">Quarantine/reject</p>
            </div>
          </div>

          <div class="row">
            <div class="col-6">
              <label for="spamSubjectTag">Subject Tag</label>
              <input id="spamSubjectTag" type="text" name="spamSubjectTag"
                value="<?= $e($policy->spamSubjectTag ?? '') ?>" placeholder="e.g. [SPAM?]" />
            </div>
            <div class="col-6">
              <label for="spamSubjectTag2">Subject Tag2</label>
              <input id="spamSubjectTag2" type="text" name="spamSubjectTag2"
                value="<?= $e($policy->spamSubjectTag2 ?? '') ?>" placeholder="e.g. [SPAM]" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Bypass & Delivery Options</legend>

          <label><input type="checkbox" name="bypassVirusChecks" <?= ($policy->bypassVirusChecks ?? false) ? 'checked' : '' ?> /> Bypass virus checks</label>
          <label><input type="checkbox" name="bypassSpamChecks" <?= ($policy->bypassSpamChecks ?? false) ? 'checked' : '' ?> /> Bypass spam checks</label>
          <label><input type="checkbox" name="virusLover" <?= ($policy->virusLover ?? false) ? 'checked' : '' ?> /> Deliver virus messages</label>
          <label><input type="checkbox" name="spamLover" <?= ($policy->spamLover ?? false) ? 'checked' : '' ?> /> Deliver spam messages</label>
          <label><input type="checkbox" name="bannedFilesLover" <?= ($policy->bannedFilesLover ?? false) ? 'checked' : '' ?> /> Deliver banned file messages</label>
          <label><input type="checkbox" name="badHeaderLover" <?= ($policy->badHeaderLover ?? false) ? 'checked' : '' ?> /> Deliver bad header messages</label>
        </fieldset>

        <button type="submit" class="button primary">Save Policy</button>

        <?php if ($policy !== null): ?>
        <button type="submit" name="action" value="delete" class="button error outline"
          data-confirm="Delete this spam policy?">Delete Policy</button>
        <?php endif; ?>
      </form>

      <?php if (!empty($policies)): ?>
      <hr />
      <h3>All Configured Policies</h3>
      <table class="striped">
        <thead>
          <tr>
            <th>Account</th>
            <th>Tag</th>
            <th>Tag2</th>
            <th>Kill</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($policies as $entry): ?>
          <tr>
            <td><a href="/amavisd/spam-policy/<?= $e($entry['account']) ?>"><?= $e($entry['account']) ?></a></td>
            <td><?= $e($entry['policy']->spamTagLevel ?? '-') ?></td>
            <td><?= $e($entry['policy']->spamTag2Level ?? '-') ?></td>
            <td><?= $e($entry['policy']->spamKillLevel ?? '-') ?></td>
            <td><a href="/amavisd/spam-policy/<?= $e($entry['account']) ?>">Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <p><a href="/amavisd/quarantine">&larr; Back to quarantine</a></p>
    </div>
  </div>
</div>
