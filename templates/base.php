<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>MailPanel - <?= $e($pageTitle) ?></title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/static/chota.min.css" />
    <link rel="stylesheet" href="/static/styles.css" />
  </head>
  <body>
    <nav class="nav">
      <div class="nav-left">
        <a class="brand" href="/"><img src="/static/logo-iredmail.png" alt="iredmail logo" /> MailPanel</a>
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
        <span class="text-light">MailPanel v<?= defined('APP_VERSION') ? APP_VERSION : '0.0.0' ?></span>
      </div>
    </footer>
  </body>
</html>
