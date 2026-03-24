<?php

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/bootstrap.php';

use App\Models\DomainSettings;
use App\Repositories\RepositoryFactory;

$options = getopt('', ['output:']);

if (empty($options['output'])) {
    echo "Usage: php cli/dumpDisclaimer.php --output=/etc/postfix/disclaimer/\n";
    echo "\nExports domain disclaimer text to files for Postfix integration.\n";
    echo "Creates {domain}.txt and {domain}.html for each domain with a disclaimer.\n";
    exit(1);
}

$outputDir = rtrim($options['output'], '/');
if (!is_dir($outputDir)) {
    echo "Error: Output directory does not exist: {$outputDir}\n";
    exit(1);
}

$domainRepo = RepositoryFactory::getDomainRepository();
$domains = $domainRepo->getDomains();
$count = 0;

foreach ($domains as $domainInfo) {
    $domainName = $domainInfo['domainName'];
    $domain = $domainRepo->getDomain($domainName);
    if ($domain === null) {
        continue;
    }

    $settings = DomainSettings::fromSettingsString($domain->settings);
    $disclaimer = $settings->disclaimer;

    $txtFile = "{$outputDir}/{$domainName}.txt";
    $htmlFile = "{$outputDir}/{$domainName}.html";

    if ($disclaimer === '') {
        // Remove old disclaimer files if no disclaimer is set
        foreach ([$txtFile, $htmlFile] as $file) {
            if (file_exists($file)) {
                unlink($file);
                echo "  - Removed {$file}\n";
            }
        }
        continue;
    }

    // Write text version
    file_put_contents($txtFile, "\n---------\n" . $disclaimer . "\n");

    // Write HTML version
    $htmlContent = '<div id="disclaimer_separator"><p>----------</p></div>';
    $htmlContent .= '<div id="disclaimer_text"><p>' . nl2br(htmlspecialchars($disclaimer, ENT_QUOTES, 'UTF-8')) . '</p></div>';
    file_put_contents($htmlFile, "\n" . $htmlContent . "\n");

    $count++;
    echo "  + {$domainName}\n";
}

echo "Done. {$count} disclaimer(s) exported to {$outputDir}\n";
