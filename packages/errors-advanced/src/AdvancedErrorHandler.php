<?php

declare(strict_types=1);

namespace Marko\ErrorsAdvanced;

use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\Errors\ErrorReport;
use Marko\ErrorsSimple\SimpleErrorHandler;
use Marko\ErrorsAdvanced\Formatters\AdvancedHtmlFormatter;
use Marko\ErrorsSimple\Environment;
use Throwable;

class AdvancedErrorHandler extends SimpleErrorHandler implements ErrorHandlerInterface
{
    private AdvancedHtmlFormatter $advancedHtmlFormatter;

    public function __construct(
        Environment $environment,
        ?AdvancedHtmlFormatter $advancedHtmlFormatter = null,
    ) {
        parent::__construct($environment);
        $this->advancedHtmlFormatter = $advancedHtmlFormatter ?? new AdvancedHtmlFormatter(
            $environment,
            new \Marko\ErrorsSimple\CodeSnippetExtractor()
        );
    }

    public function handle(
        ErrorReport $report,
    ): void {
        $this->clearOutputBuffers();

        try {
            if ($this->environment->isCli()) {
                // Fall back to simple text formatter for CLI as it's already quite good
                parent::handle($report);
            } else {
                $this->setHttpStatusCode(500);
                echo $this->advancedHtmlFormatter->format($report);
            }
        } catch (Throwable) {
            echo "Error: $report->message\n";
        }
    }
}
