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
        </div>
      </div>
    </div>
  </div>
</div>
