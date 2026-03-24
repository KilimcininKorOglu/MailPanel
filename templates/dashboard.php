<?php $pageTitle = 'Dashboard'; ?>
<div class="container">
  <h1>Dashboard</h1>

  <div class="row">
    <div class="col-4">
      <div class="card">
        <header>
          <h4>Domains</h4>
        </header>
        <p>
          Total: <strong><?= $e($stats['totalDomains']) ?></strong><br />
          Active: <?= $e($stats['activeDomains']) ?><br />
          Disabled: <?= $e($stats['totalDomains'] - $stats['activeDomains']) ?>
        </p>
        <a href="/domains" class="button primary outline">Manage domains</a>
      </div>
    </div>

    <div class="col-4">
      <div class="card">
        <header>
          <h4>Users</h4>
        </header>
        <p>
          Total: <strong><?= $e($stats['totalUsers']) ?></strong><br />
          Active: <?= $e($stats['activeUsers']) ?><br />
          Disabled: <?= $e($stats['totalUsers'] - $stats['activeUsers']) ?>
        </p>
      </div>
    </div>

    <div class="col-4">
      <div class="card">
        <header>
          <h4>Admins</h4>
        </header>
        <p>
          Total: <strong><?= $e($stats['totalAdmins']) ?></strong>
        </p>
        <a href="/admins" class="button primary outline">Manage admins</a>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top: 1rem;">
    <div class="col-6">
      <div class="card">
        <header>
          <h4>Quota</h4>
        </header>
        <p>
          Allocated: <strong><?= $e(number_format($stats['totalQuotaAllocated'])) ?> MB</strong><br />
          Used: <?= $e(number_format($stats['totalQuotaUsed'])) ?> MB
        </p>
      </div>
    </div>

    <div class="col-6">
      <div class="card">
        <header>
          <h4>Messages</h4>
        </header>
        <p>
          Total stored: <strong><?= $e(number_format($stats['totalMessages'])) ?></strong>
        </p>
      </div>
    </div>
  </div>
</div>
