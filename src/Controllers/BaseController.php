<?php

declare(strict_types=1);

namespace App\Controllers;

use App\TemplateEngine;

class BaseController
{
    /**
     * Renders the 404 error page.
     */
    public static function page404(TemplateEngine $tpl): void
    {
        http_response_code(404);
        $tpl->render('page404.php');
    }
}
