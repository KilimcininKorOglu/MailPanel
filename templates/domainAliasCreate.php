<?php $pageTitle = 'Create Domain Alias'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>Create domain alias</h1>

      <div class="row breadcrumbs">
        <div class="col">
          <a href="/domain-aliases">Domain Aliases</a> /
          <span class="text-light">Create</span>
        </div>
      </div>

      <?php if (!empty($error)): ?>
      <p class="text-error"><?= $e($error) ?></p>
      <?php endif; ?>

      <form method="post">
        <?= $csrfField ?>

        <p>
          <label for="aliasDomain">Alias domain name</label>
          <input id="aliasDomain" type="text" name="aliasDomain" required placeholder="alias.example.com"
            <?php if (!empty($validationErrors['aliasDomain'])): ?>class="error"<?php endif; ?>
            value="<?= $e($alias?->aliasDomain ?? '') ?>"
          />
          <?php if (!empty($validationErrors['aliasDomain'])): ?>
          <span class="text-error"><?= $e($validationErrors['aliasDomain']) ?></span>
          <?php endif; ?>
        </p>

        <p>
          <label for="targetDomain">Target domain</label>
          <select id="targetDomain" name="targetDomain" required
            <?php if (!empty($validationErrors['targetDomain'])): ?>class="error"<?php endif; ?>
          >
            <option value="">-- Select target domain --</option>
            <?php foreach ($allDomains as $d): ?>
            <option value="<?= $e($d['domainName']) ?>"
              <?php if (($alias?->targetDomain ?? '') === $d['domainName']): ?>selected<?php endif; ?>
            ><?= $e($d['domainName']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($validationErrors['targetDomain'])): ?>
          <span class="text-error"><?= $e($validationErrors['targetDomain']) ?></span>
          <?php endif; ?>
        </p>

        <p>
          <label>
            <input type="checkbox" name="active" <?php if ($alias === null || ($alias->active ?? true)): ?>checked<?php endif; ?> />
            Active
          </label>
        </p>

        <button type="submit" class="button primary">Create alias</button>
        <a href="/domain-aliases" class="button outline">Cancel</a>
      </form>
    </div>
  </div>
</div>
