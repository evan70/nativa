<?php

declare(strict_types=1);

namespace App\Htmx;

use Marko\Routing\Http\Request;

readonly class HtmxContext
{
    private function __construct(
        private Request $request,
    ) {}

    public static function fromRequest(Request $request): ?self
    {
        if ($request->header('HX-Request') !== 'true') {
            return null;
        }

        return new self($request);
    }

    public function target(): ?string
    {
        return $this->request->header('HX-Target');
    }

    public function trigger(): ?string
    {
        return $this->request->header('HX-Trigger');
    }

    public function triggerName(): ?string
    {
        return $this->request->header('HX-Trigger-Name');
    }

    public function currentUrl(): ?string
    {
        return $this->request->header('HX-Current-Url');
    }

    public function isSwap(): bool
    {
        return $this->request->header('HX-Target') !== null;
    }
}