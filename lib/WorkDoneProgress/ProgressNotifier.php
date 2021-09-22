<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

interface ProgressNotifier
{
    /**
     * @param int|null $percentage Percentage comprised between 0 and 100
     */
    public function begin(
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void;

    /**
     * @param int|null $percentage Percentage comprised between 0 and 100
     */
    public function report(
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void;

    public function end(?string $message = null): void;
}
