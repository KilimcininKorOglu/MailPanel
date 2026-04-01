<?php $pageTitle = 'User'; ?>
<div class="container">
  <div class="row">
    <div class="col">
      <h1><?= $e($user->uid) ?></h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domains"><?= $e($domain) ?></a> /
          <a href="/<?= $e($domain) ?>/users">Users</a> /
          <span class="text-light"><?= $e($user->uid) ?></span>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <nav class="tabs">
            <a
              <?php if ($editMode === 'general'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/general"
            >General information</a>
            <a
              <?php if ($editMode === 'password'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/password"
            >Password</a>
            <a
              <?php if ($editMode === 'services'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/services"
            >Services</a>
            <a
              <?php if ($editMode === 'forwarding'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/forwarding"
            >Forwarding</a>
            <a
              <?php if ($editMode === 'aliases'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/aliases"
            >Aliases</a>
            <a
              <?php if ($editMode === 'bcc'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/bcc"
            >BCC</a>
            <a
              <?php if ($editMode === 'relay'): ?>class="active"<?php endif; ?>
              href="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/relay"
            >Relay</a>
          </nav>
        </div>
      </div>

      <div class="row">
        <div class="col-8 col-6-md">
          <?php if (!empty($error)): ?>
          <p class="text-error"><?= $e($error) ?></p>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
          <p class="text-success"><?= $e($success) ?></p>
          <?php endif; ?>

          <form method="post">
            <?= $csrfField ?>

            <?php if ($editMode === 'general'): ?>
            <input type="hidden" value="<?= $e($user->uid) ?>" name="uid" />

            <?php if (!empty($session['isGlobalAdmin'])): ?>
            <details style="margin-bottom:1rem;">
              <summary>Rename email address</summary>
              <form method="post" action="/<?= $e($domain) ?>/users/<?= $e($user->uid) ?>/rename" style="margin-top:0.5rem;" data-confirm="Rename user email?">
                <?= $csrfField ?>
                <div class="row">
                  <div class="col-6">
                    <input type="text" name="newUid" placeholder="new-username" required />
                  </div>
                  <div class="col-3">
                    <span>@<?= $e($domain) ?></span>
                  </div>
                  <div class="col-3">
                    <button type="submit" class="button outline">Rename</button>
                  </div>
                </div>
              </form>
            </details>
            <?php endif; ?>

            <div class="row">
              <div class="col">
                <p>
                  <label for="accountStatus">
                    <input id="accountStatus" name="accountStatus"
                    type="checkbox" <?php if ($user->accountStatus): ?>checked<?php endif; ?>> Record active
                  </label>
                </p>
                <p>
                  <label for="mailQuota">Quota, MB</label>
                  <input
                    id="mailQuota"
                    name="mailQuota"
                    type="number"
                    value="<?= $e($user->mailQuota) ?>"
                    required
                  />
                </p>

                <p>
                  <label for="cn">Full name</label>
                  <input id="cn" name="cn" type="text" value="<?= $e($user->cn) ?>" />
                </p>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <p>
                  <label for="givenName">First name</label>
                  <input
                    id="givenName"
                    name="givenName"
                    type="text"
                    value="<?= $e($user->givenName) ?>"
                  />
                </p>
              </div>
              <div class="col">
                <p>
                  <label for="sn">Last name</label>
                  <input id="sn" name="sn" type="text" value="<?= $e($user->sn) ?>" />
                </p>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <p>
                  <label for="employeeNumber">Employee number</label>
                  <input
                    id="employeeNumber"
                    name="employeeNumber"
                    type="text"
                    value="<?= $e($user->employeeNumber) ?>"
                  />
                </p>
                <p>
                  <label for="title">Position</label>
                  <input
                    id="title"
                    name="title"
                    type="text"
                    value="<?= $e($user->title) ?>"
                  />
                </p>
                <p>
                  <label for="mobile">Mobile phone</label>
                  <input
                    id="mobile"
                    name="mobile"
                    type="text"
                    value="<?= $e($user->mobile) ?>"
                  />
                </p>
                <p>
                  <label for="telephoneNumber">Work phone</label>
                  <input
                    id="telephoneNumber"
                    name="telephoneNumber"
                    type="text"
                    value="<?= $e($user->telephoneNumber) ?>"
                  />
                </p>
                <p>
                  <label for="domainGlobalAdmin">
                    <input id="domainGlobalAdmin" name="domainGlobalAdmin"
                    type="checkbox" <?php if ($user->domainGlobalAdmin): ?>checked<?php endif; ?>> Global administrator
                  </label>
                </p>
                <p>
                  <button type="submit" class="button primary">
                    Save
                  </button>
                </p>
              </div>
            </div>

            <?php elseif ($editMode === 'password'): ?>
            <?php if (!empty($requireOldPassword)): ?>
            <p>
              <label for="old_password">Current password</label>
              <input name="old_password" type="password" id="old_password" required
                <?php if (!empty($validationErrors['old_password'])): ?>class="error"<?php endif; ?>
              />
              <?php if (!empty($validationErrors['old_password'])): ?>
              <p class="text-error"><?= $e($validationErrors['old_password']) ?></p>
              <?php endif; ?>
            </p>
            <?php endif; ?>
            <p>
              <label for="password">Password</label>
              <input name="password" type="password" id="password" required autocomplete="new-password"
                <?php if (!empty($validationErrors['password'])): ?>class="error"<?php endif; ?>
              />
              <?php if (!empty($validationErrors['password'])): ?>
              <p class="text-error"><?= $e($validationErrors['password']) ?></p>
              <?php endif; ?>
            </p>
            <p>
              <label for="password_repeat">Password (repeat)</label>
              <input name="password_repeat" type="password" id="password_repeat" required
                <?php if (!empty($validationErrors['password_repeat'])): ?>class="error"<?php endif; ?>
              />
              <?php if (!empty($validationErrors['password_repeat'])): ?>
              <p class="text-error"><?= $e($validationErrors['password_repeat']) ?></p>
              <?php endif; ?>
            </p>
            <p>
              <button type="button" class="button outline" onclick="generatePassword()">Generate password</button>
            </p>
            <p>
              <button type="submit" class="button primary">
                Save
              </button>
            </p>

            <?php elseif ($editMode === 'services'): ?>
            <h3>Mail services</h3>
            <p>
              <label><input type="checkbox" name="enableSmtp" <?php if ($user->enableSmtp): ?>checked<?php endif; ?> /> SMTP</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableSmtpSecured" <?php if ($user->enableSmtpSecured): ?>checked<?php endif; ?> /> SMTP (TLS)</label>
            </p>
            <p>
              <label><input type="checkbox" name="enablePop3" <?php if ($user->enablePop3): ?>checked<?php endif; ?> /> POP3</label>
            </p>
            <p>
              <label><input type="checkbox" name="enablePop3Secured" <?php if ($user->enablePop3Secured): ?>checked<?php endif; ?> /> POP3 (TLS)</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableImap" <?php if ($user->enableImap): ?>checked<?php endif; ?> /> IMAP</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableImapSecured" <?php if ($user->enableImapSecured): ?>checked<?php endif; ?> /> IMAP (TLS)</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableManagesieve" <?php if ($user->enableManagesieve): ?>checked<?php endif; ?> /> ManageSieve</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableManagesieveSecured" <?php if ($user->enableManagesieveSecured): ?>checked<?php endif; ?> /> ManageSieve (TLS)</label>
            </p>
            <p>
              <label><input type="checkbox" name="enableSogo" <?php if ($user->enableSogo): ?>checked<?php endif; ?> /> SOGo Webmail</label>
            </p>
            <p>
              <button type="submit" class="button primary">Save services</button>
            </p>

            <?php elseif ($editMode === 'forwarding'): ?>
            <h3>Email forwarding</h3>
            <p>
              <label for="forwardingAddresses">Forwarding addresses (one per line)</label>
              <textarea id="forwardingAddresses" name="forwardingAddresses" rows="5" placeholder="user@example.com"><?= $e(implode("\n", $forwardings ?? [])) ?></textarea>
            </p>
            <p>
              <label>
                <input type="checkbox" name="keepCopy" <?php if ($keepCopy ?? true): ?>checked<?php endif; ?> />
                Keep a local copy of forwarded messages
              </label>
            </p>
            <p>
              <button type="submit" class="button primary">Save forwarding</button>
            </p>
            <?php endif; ?>
          </form>

          <?php if ($editMode === 'aliases'): ?>
          <h3>Per-user Alias Addresses</h3>
          <p class="text-light">Additional email addresses that deliver to this mailbox.</p>

          <form method="post">
            <?= $csrfField ?>
            <input type="hidden" name="action" value="add" />
            <div class="row">
              <div class="col-8">
                <input type="email" name="newAlias" placeholder="alias@example.com" required />
              </div>
              <div class="col-4">
                <button type="submit" class="button primary outline">Add Alias</button>
              </div>
            </div>
          </form>

          <?php if (!empty($userAliases)): ?>
          <table class="striped" style="margin-top:1rem;">
            <thead>
              <tr>
                <th>Alias Address</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($userAliases as $aliasAddr): ?>
              <tr>
                <td><?= $e($aliasAddr) ?></td>
                <td>
                  <form method="post" style="display:inline">
                    <?= $csrfField ?>
                    <input type="hidden" name="action" value="remove" />
                    <input type="hidden" name="aliasAddress" value="<?= $e($aliasAddr) ?>" />
                    <button type="submit" class="button error outline" data-confirm="Remove <?= $e($aliasAddr) ?>?">Remove</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p class="text-light" style="margin-top:1rem;">No alias addresses configured for this user.</p>
          <?php endif; ?>
          <?php endif; ?>

          <?php if ($editMode === 'bcc'): ?>
          <form method="post">
            <?= $csrfField ?>
            <h3>BCC Settings</h3>
            <p class="text-light">BCC copies of sent or received emails to the specified addresses.</p>

            <label for="senderBcc">Sender BCC (outbound mail copy)</label>
            <input id="senderBcc" type="email" name="senderBcc"
              value="<?= $e($userSenderBcc ?? '') ?>"
              placeholder="Leave empty to disable sender BCC"
            />

            <label for="recipientBcc">Recipient BCC (inbound mail copy)</label>
            <input id="recipientBcc" type="email" name="recipientBcc"
              value="<?= $e($userRecipientBcc ?? '') ?>"
              placeholder="Leave empty to disable recipient BCC"
            />

            <p><button type="submit" class="button primary">Save BCC settings</button></p>
          </form>
          <?php endif; ?>

          <?php if ($editMode === 'relay'): ?>
          <form method="post">
            <?= $csrfField ?>
            <h3>Sender-Dependent Relay</h3>
            <p class="text-light">Route outbound mail from this user through a specific relay server.</p>

            <label for="relayhost">Relay Host</label>
            <input id="relayhost" type="text" name="relayhost"
              value="<?= $e($userRelayhost ?? '') ?>"
              placeholder="[smtp.relay.com]:587"
            />
            <p class="text-light">Format: <code>[hostname]:port</code> — square brackets prevent MX lookup.</p>

            <p><button type="submit" class="button primary">Save relay settings</button></p>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
