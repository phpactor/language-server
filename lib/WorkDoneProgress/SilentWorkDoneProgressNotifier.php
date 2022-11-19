<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Amp\Promise;
use Amp\Success;
use Ramsey\Uuid\Uuid;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class SilentWorkDoneProgressNotifier implements ProgressNotifier
{
    public function create(WorkDoneToken $token): Promise
    {
        return new Success(new ResponseMessage(
            Uuid::uuid4(),
            null,
        ));
    }

    public function begin(WorkDoneToken $token, string $title, ?string $message = null, ?int $percentage = null, ?bool $cancellable = null): void
    {
    }

    public function report(WorkDoneToken $token, ?string $message = null, ?int $percentage = null, ?bool $cancellable = null): void
    {
    }

    public function end(WorkDoneToken $token, ?string $message = null): void
    {
    }
}
