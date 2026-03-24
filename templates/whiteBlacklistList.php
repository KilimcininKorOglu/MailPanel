<?php $pageTitle = 'White/Blacklist: ' . ($account ?? ''); ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>White/Blacklist</h1>

      <?php if (!empty($success)): ?>
      <div class="card bg-success text-white"><?= $e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
      <div class="card bg-error text-white"><?= $e($error) ?></div>
      <?php endif; ?>

      <p>
        <strong>Account:</strong> <?= $e($account) ?>
        <?php if ($account === '@.'): ?>(Global)<?php endif; ?>
      </p>

      <form method="get" action="/amavisd/wblist" style="margin-bottom:1rem;">
        <div class="row">
          <div class="col-8">
            <input type="text" name="account" value="<?= $e($account !== '@.' ? $account : '') ?>" placeholder="@. (global), @domain.com, or user@domain.com" />
          </div>
          <div class="col-4">
            <button type="submit" class="button outline">Load List</button>
          </div>
        </div>
      </form>

      <!-- Inbound -->
      <h3>Inbound White/Blacklist</h3>

      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="direction" value="inbound" />
        <div class="row">
          <div class="col-5">
            <input type="text" name="sender" placeholder="user@example.com or @domain.com" required />
          </div>
          <div class="col-3">
            <select name="wb">
              <option value="W">Whitelist</option>
              <option value="B">Blacklist</option>
            </select>
          </div>
          <div class="col-4">
            <button type="submit" class="button primary outline">Add</button>
          </div>
        </div>
      </form>

      <?php if (!empty($inboundList)): ?>
      <table class="striped" style="margin-top:1rem;">
        <thead>
          <tr>
            <th>Sender</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inboundList as $entry): ?>
          <tr>
            <td><?= $e($entry['sender']) ?></td>
            <td><?= $entry['wb'] === 'W' ? 'Whitelist' : 'Blacklist' ?></td>
            <td>
              <form method="post" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="remove" />
                <input type="hidden" name="direction" value="inbound" />
                <input type="hidden" name="sender" value="<?= $e($entry['sender']) ?>" />
                <button type="submit" class="button error outline">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p class="text-light">No inbound entries.</p>
      <?php endif; ?>

      <hr />

      <!-- Outbound -->
      <h3>Outbound White/Blacklist</h3>

      <form method="post">
        <?= $csrfField ?>
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="direction" value="outbound" />
        <div class="row">
          <div class="col-5">
            <input type="text" name="sender" placeholder="user@example.com or @domain.com" required />
          </div>
          <div class="col-3">
            <select name="wb">
              <option value="W">Whitelist</option>
              <option value="B">Blacklist</option>
            </select>
          </div>
          <div class="col-4">
            <button type="submit" class="button primary outline">Add</button>
          </div>
        </div>
      </form>

      <?php if (!empty($outboundList)): ?>
      <table class="striped" style="margin-top:1rem;">
        <thead>
          <tr>
            <th>Recipient</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($outboundList as $entry): ?>
          <tr>
            <td><?= $e($entry['sender']) ?></td>
            <td><?= $entry['wb'] === 'W' ? 'Whitelist' : 'Blacklist' ?></td>
            <td>
              <form method="post" style="display:inline">
                <?= $csrfField ?>
                <input type="hidden" name="action" value="remove" />
                <input type="hidden" name="direction" value="outbound" />
                <input type="hidden" name="sender" value="<?= $e($entry['sender']) ?>" />
                <button type="submit" class="button error outline">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p class="text-light">No outbound entries.</p>
      <?php endif; ?>

      <p><a href="/amavisd/quarantine">&larr; Back to quarantine</a></p>
    </div>
  </div>
</div>
