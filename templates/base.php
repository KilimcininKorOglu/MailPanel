<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?= $e($brand['name'] ?? 'MailPanel') ?> - <?= $e($pageTitle) ?></title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/static/chota.min.css" />
    <link rel="stylesheet" href="/static/styles.css" />
  </head>
  <body>
    <nav class="nav">
      <div class="nav-left">
        <a class="brand" href="/"><img src="<?= $e($brand['logoUrl'] ?? '/static/logo-iredmail.png') ?>" alt="logo" /> <?= $e($brand['name'] ?? 'MailPanel') ?></a>
        <?php if (!empty($session['email'])): ?>
        <a href="/dashboard">Dashboard</a>
        <a href="/domains">Domains</a>
        <?php if (!empty($session['isGlobalAdmin'])): ?>
        <a href="/admins">Admins</a>
        <a href="/logs">Logs</a>
        <?php endif; ?>
        <?php endif; ?>
      </div>
      <div class="nav-right">
        <?php if (!empty($session['email'])): ?>
        <a class="button outline" href="/logout">Logout <?= $e($session['email']) ?></a>
        <?php endif; ?>
      </div>
    </nav>
    <?= $bodyContent ?>
    <footer class="footer">
      <div class="container">
        <?php if (!empty($brand['footerText'])): ?>
        <span class="text-light"><?= $e($brand['footerText']) ?></span> |
        <?php endif; ?>
        <a href="https://github.com/KilimcininKorOglu/MailPanel" class="text-light" target="_blank"><?= $e($brand['name'] ?? 'MailPanel') ?> v<?= defined('APP_VERSION') ? APP_VERSION : '0.0.0' ?></a>
      </div>
    </footer>
  </body>
</html>
