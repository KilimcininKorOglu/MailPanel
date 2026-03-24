<?php

declare(strict_types=1);

namespace App;

class TemplateEngine
{
    private string $templateDir;

    public function __construct(string $templateDir)
    {
        $this->templateDir = rtrim($templateDir, '/');
    }

    /**
     * Renders a child template wrapped in the base layout.
     *
     * The child template should set $pageTitle and output body HTML.
     * The engine captures the body via output buffering and includes base.php.
     */
    public function render(string $template, array $vars = []): void
    {
        $vars['session'] = $_SESSION ?? [];

        // Make helper functions available in template scope
        $e = function (mixed $value): string {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        $localize = [TemplateFilters::class, 'localize'];
        $asMegabytes = [TemplateFilters::class, 'asMegabytes'];

        extract($vars, EXTR_SKIP);

        $pageTitle = '';

        ob_start();
        include $this->templateDir . '/' . $template;
        $bodyContent = ob_get_clean();

        include $this->templateDir . '/base.php';
    }
}
