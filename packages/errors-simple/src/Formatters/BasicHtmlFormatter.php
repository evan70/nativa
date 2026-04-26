<?php

declare(strict_types=1);

namespace Marko\ErrorsSimple\Formatters;

use Marko\Errors\ErrorReport;
use Marko\ErrorsSimple\CodeSnippetExtractor;
use Marko\ErrorsSimple\Environment;

class BasicHtmlFormatter
{
    public const string CONTENT_TYPE = 'text/html; charset=UTF-8';

    public function __construct(
        private Environment $environment,
        private CodeSnippetExtractor $extractor,
    ) {}

    public function format(
        ErrorReport $report,
    ): string {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: sans-serif; padding: 40px; line-height: 1.5; color: #333; }
        h1 { color: #c00; }
    </style>
</head>
<body>
    <h1>Something went wrong</h1>
    <p>We're sorry, but an unexpected error occurred. Please try again later.</p>
</body>
</html>
HTML;
    }
}
