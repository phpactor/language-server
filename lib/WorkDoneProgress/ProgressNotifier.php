<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface ProgressNotifier
{
    /**
     * @return Promise<ResponseMessage>
     */
    public function create(WorkDoneToken $token): Promise;

    /**
     * @param int|null $percentage Percentage comprised between 0 and 100
     */
    public function begin(
        WorkDoneToken $token,
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void;

    /**
     * @param int|null $percentage Percentage comprised between 0 and 100
     */
    public function report(
        WorkDoneToken $token,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void;

    public function end(WorkDoneToken $token, ?string $message = null): void;
}
