<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Insights;

use NunoMaduro\PhpInsights\Domain\Contracts\HasDetails;
use NunoMaduro\PhpInsights\Domain\Details;

final class ForbiddenGlobals extends Insight implements HasDetails
{
    public function hasIssue(): bool
    {
        return count($this->getDetails()) > 0;
    }

    public function getTitle(): string
    {
        return "{$this->collector->getGlobalAccesses()} globals accesses detected";
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails(): array
    {
        $details = [];

        foreach ($this->collector->getGlobalVariableAccesses() as $file => $global) {
            if ($this->shouldSkipFile($file)) {
                continue;
            }
            $details[] = Details::make()->setFile($file)->setMessage(
                "Usage of ${global} found; Usage of GLOBALS are discouraged consider not relying on global scope"
            );
        }

        return $details;
    }
}
