<?php $pageTitle = 'System Settings'; ?>
<div class="container">
  <div class="row">
    <div class="col-8">
      <h1>System Settings</h1>

      <p class="text-light">These settings are configured via environment variables and cannot be changed from this panel.</p>

      <table class="striped">
        <thead>
          <tr>
            <th>Setting</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Backend</td>
            <td><code><?= $e($backend) ?></code></td>
          </tr>
          <tr>
            <td>Password Scheme</td>
            <td><code><?= $e($passwordScheme) ?></code></td>
          </tr>
          <tr>
            <td>Password Min Length</td>
            <td><?= $e($passwordMinLength) ?></td>
          </tr>
          <tr>
            <td>Pagination Per Page</td>
            <td><?= $e($paginationPerPage) ?></td>
          </tr>
          <tr>
            <td>Session Timeout</td>
            <td><?= $e($sessionTimeout) ?> seconds</td>
          </tr>
          <tr>
            <td>Allowed IP Ranges</td>
            <td><?= $e($allowedIpRanges !== '' ? $allowedIpRanges : 'All') ?></td>
          </tr>
          <tr>
            <td>Session IP Validation</td>
            <td><?= $sessionValidateIp ? 'Enabled' : 'Disabled' ?></td>
          </tr>
          <tr>
            <td>Update Check</td>
            <td><?= $checkUpdates ? 'Enabled' : 'Disabled' ?></td>
          </tr>
          <tr>
            <td>Amavisd Integration</td>
            <td><?= $amavisdEnabled ? 'Enabled' : 'Disabled' ?></td>
          </tr>
          <tr>
            <td>Fail2ban Integration</td>
            <td><?= $fail2banEnabled ? 'Enabled' : 'Disabled' ?></td>
          </tr>
          <tr>
            <td>iRedAPD Integration</td>
            <td><?= $iredapdEnabled ? 'Enabled' : 'Disabled' ?></td>
          </tr>
        </tbody>
      </table>

      <h3>Quick Links</h3>
      <ul>
        <li><a href="/last-logins">Last Login Tracking</a></li>
        <li><a href="/export/admins">Export Admin Statistics (CSV)</a></li>
        <li><a href="/export/admins?format=json">Export Admin Statistics (JSON)</a></li>
      </ul>
    </div>
  </div>
</div>
