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

      <nav class="tabs">
        <a <?php if (($editMode ?? 'general') === 'general'): ?>class="active"<?php endif; ?>
           href="/domains/<?= $e($domain->domainName) ?>/edit">General</a>
        <a <?php if (($editMode ?? '') === 'settings'): ?>class="active"<?php endif; ?>
           href="/domains/<?= $e($domain->domainName) ?>/settings">Settings</a>
        <a <?php if (($editMode ?? '') === 'catchall'): ?>class="active"<?php endif; ?>
           href="/domains/<?= $e($domain->domainName) ?>/catchall">Catch-all</a>
        <a <?php if (($editMode ?? '') === 'bcc'): ?>class="active"<?php endif; ?>
           href="/domains/<?= $e($domain->domainName) ?>/bcc">BCC</a>
        <a <?php if (($editMode ?? '') === 'relay'): ?>class="active"<?php endif; ?>
           href="/domains/<?= $e($domain->domainName) ?>/relay">Relay</a>
      </nav>

      <p class="text-light">
        Users: <?= $e($domain->currentUserCount) ?> |
        Created: <?= $e($domain->created ?? 'N/A') ?>
      </p>

      <?php if (($editMode ?? 'general') === 'general'): ?>
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

      <?php elseif (($editMode ?? '') === 'settings'): ?>
      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="defaultUserQuota">Default user quota (MB, 0 = use global)</label>
          <input id="defaultUserQuota" type="number" name="defaultUserQuota" min="0"
            value="<?= $e($domainSettings->defaultUserQuota ?? 0) ?>"
          />
        </p>

        <div class="row">
          <div class="col-6">
            <p>
              <label for="minPasswordLength">Min password length (0 = use global)</label>
              <input id="minPasswordLength" type="number" name="minPasswordLength" min="0"
                value="<?= $e($domainSettings->minPasswordLength ?? 0) ?>"
              />
            </p>
          </div>
          <div class="col-6">
            <p>
              <label for="maxPasswordLength">Max password length (0 = unlimited)</label>
              <input id="maxPasswordLength" type="number" name="maxPasswordLength" min="0"
                value="<?= $e($domainSettings->maxPasswordLength ?? 0) ?>"
              />
            </p>
          </div>
        </div>

        <p>
          <label for="disclaimer">Disclaimer text</label>
          <textarea id="disclaimer" name="disclaimer" rows="5"><?= $e($domainSettings->disclaimer ?? '') ?></textarea>
        </p>

        <button type="submit" class="button primary">Save settings</button>
      </form>

      <?php elseif (($editMode ?? '') === 'catchall'): ?>
      <form method="post">
        <?= $csrfField ?>

        <fieldset>
          <legend>Catch-all Address</legend>
          <p class="text-light">A catch-all address receives all emails sent to non-existent addresses under this domain.</p>

          <label for="catchallTarget">Forward to email address</label>
          <input id="catchallTarget" type="email" name="catchallTarget"
            value="<?= $e($catchallTarget ?? '') ?>"
            placeholder="Leave empty to disable catch-all"
          />
          <p class="text-light">Leave empty and save to remove the catch-all address.</p>
        </fieldset>

        <button type="submit" class="button primary">Save catch-all</button>
      </form>

      <?php elseif (($editMode ?? '') === 'bcc'): ?>
      <form method="post">
        <?= $csrfField ?>

        <fieldset>
          <legend>BCC Settings</legend>
          <p class="text-light">BCC copies of all sent or received emails to the specified addresses.</p>

          <label for="senderBcc">Sender BCC (outbound mail copy)</label>
          <input id="senderBcc" type="email" name="senderBcc"
            value="<?= $e($senderBcc ?? '') ?>"
            placeholder="Leave empty to disable sender BCC"
          />

          <label for="recipientBcc">Recipient BCC (inbound mail copy)</label>
          <input id="recipientBcc" type="email" name="recipientBcc"
            value="<?= $e($recipientBcc ?? '') ?>"
            placeholder="Leave empty to disable recipient BCC"
          />
        </fieldset>

        <button type="submit" class="button primary">Save BCC settings</button>
      </form>

      <?php elseif (($editMode ?? '') === 'relay'): ?>
      <form method="post">
        <?= $csrfField ?>

        <fieldset>
          <legend>Sender-Dependent Relay</legend>
          <p class="text-light">Route outbound mail from this domain through a specific relay server.</p>

          <label for="relayhost">Relay Host</label>
          <input id="relayhost" type="text" name="relayhost"
            value="<?= $e($domainRelayhost ?? '') ?>"
            placeholder="[smtp.relay.com]:587"
          />
          <p class="text-light">Format: <code>[hostname]:port</code> — square brackets prevent MX lookup.</p>
        </fieldset>

        <button type="submit" class="button primary">Save relay settings</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
