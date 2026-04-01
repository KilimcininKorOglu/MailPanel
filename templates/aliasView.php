<?php $pageTitle = 'Mail Alias: ' . ($alias->address ?? ''); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Mail Alias: <?= $e($alias->address) ?></h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <nav class="tabs">
        <a class="active" href="#settings">Settings</a>
        <a href="#members">Members (<?= count($members) ?>)</a>
        <a href="#moderators">Moderators (<?= count($moderators) ?>)</a>
      </nav>

      <!-- Settings -->
      <form method="post" action="/aliases/<?= $e($alias->address) ?>">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="updateSettings" />

        <fieldset>
          <legend>Alias Settings</legend>

          <label for="name">Display Name</label>
          <input type="text" id="name" name="name" value="<?= $e($alias->name) ?>" />

          <label for="accessPolicy">Access Policy</label>
          <select id="accessPolicy" name="accessPolicy">
            <option value="public" <?= $alias->accessPolicy === 'public' ? 'selected' : '' ?>>Public (anyone can send)</option>
            <option value="domain" <?= $alias->accessPolicy === 'domain' ? 'selected' : '' ?>>Domain (only same domain)</option>
            <option value="membersOnly" <?= $alias->accessPolicy === 'membersOnly' ? 'selected' : '' ?>>Members Only</option>
            <option value="moderatorsOnly" <?= $alias->accessPolicy === 'moderatorsOnly' ? 'selected' : '' ?>>Moderators Only</option>
          </select>

          <label>
            <input type="checkbox" name="active" <?= $alias->active ? 'checked' : '' ?> />
            Active
          </label>

          <label for="members">Members (one per line)</label>
          <textarea id="members" name="members" rows="8"><?= $e(implode("\n", $members)) ?></textarea>
        </fieldset>

        <button type="submit" class="button primary">Save Settings</button>
      </form>

      <hr />

      <!-- Quick Add Member -->
      <form method="post" action="/aliases/<?= $e($alias->address) ?>">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="addMember" />

        <fieldset>
          <legend>Quick Add Member</legend>
          <div class="row">
            <div class="col-8">
              <input type="email" name="newMember" placeholder="user@example.com" required />
            </div>
            <div class="col-4">
              <button type="submit" class="button primary outline">Add Member</button>
            </div>
          </div>
        </fieldset>
      </form>

      <!-- Current Members List -->
      <?php if (!empty($members)): ?>
      <h3>Current Members</h3>
      <table class="striped">
        <thead>
          <tr>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $member): ?>
          <tr>
            <td><?= $e($member) ?></td>
            <td>
              <form method="post" action="/aliases/<?= $e($alias->address) ?>" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="removeMember" />
                <input type="hidden" name="member" value="<?= $e($member) ?>" />
                <button type="submit" class="button error outline" data-confirm="Remove <?= $e($member) ?>?">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <hr />

      <!-- Moderators -->
      <form method="post" action="/aliases/<?= $e($alias->address) ?>">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="updateModerators" />

        <fieldset>
          <legend>Moderators</legend>
          <label for="moderators">Moderator email addresses (one per line)</label>
          <textarea id="moderators" name="moderators" rows="4"><?= $e(implode("\n", $moderators)) ?></textarea>
          <p class="text-light">Moderators can approve messages when access policy is set to "Moderators Only".</p>
        </fieldset>

        <button type="submit" class="button primary outline">Save Moderators</button>
      </form>

      <hr />

      <!-- Delete -->
      <?php if (!empty($session['isGlobalAdmin'])): ?>
      <form method="post" action="/aliases/<?= $e($alias->address) ?>/delete" data-confirm="Permanently delete alias <?= $e($alias->address) ?>?">
        <?= $csrfField ?>
        <button type="submit" class="button error">Delete Alias</button>
      </form>
      <?php endif; ?>

      <p><a href="/aliases">&larr; Back to aliases</a></p>
    </div>
  </div>
</div>
