<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\Client\MessageClient;
use Ramsey\Uuid\Uuid;

final class MessageProgressNotifier implements ProgressNotifier
{
    /**
     * @var MessageClient
     */
    private $api;

    public function __construct(ClientApi $api)
    {
        $this->api = $api->window()->showMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function create(WorkDoneToken $token): Promise
    {
        return new Success(new ResponseMessage(
            Uuid::uuid4(),
            null,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function begin(
        WorkDoneToken $token,
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        $this->api->info($message);
    }

    /**
     * {@inheritDoc}
     */
    public function report(WorkDoneToken $token, ?string $message = null, ?int $percentage = null, ?bool $cancellable = null): void
    {
        $this->api->info(sprintf('%s - %d%%', $message, $percentage));
    }

    public function end(WorkDoneToken $token, ?string $message = null): void
    {
        $this->api->info($message);
    }
}
