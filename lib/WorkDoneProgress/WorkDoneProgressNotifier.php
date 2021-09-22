<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\Client\WorkDoneProgressClient;
use RuntimeException;
use function Amp\Promise\wait;

final class WorkDoneProgressNotifier implements ProgressNotifier
{
    /**
     * @var WorkDoneProgressClient
     */
    private $api;

    /**
     * @var WorkDoneToken|null
     */
    private $token;

    public function __construct(ClientApi $api, ?WorkDoneToken $token = null)
    {
        $this->api = $api->workDoneProgress();
        $this->token = $token;

        if (!$this->token) {
            $this->create();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function begin(
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        if (!$this->token) {
            return;
        }

        $this->api->begin($this->token, $title, $message, $percentage, $cancellable);
    }

    /**
     * {@inheritDoc}
     */
    public function report(
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        if (!$this->token) {
            return;
        }

        $this->api->report($this->token, $message, $percentage, $cancellable);
    }

    public function end(?string $message = null): void
    {
        if (!$this->token) {
            return;
        }

        $this->api->end($this->token, $message);
        $this->token = null;
    }

    /**
     * @throws RuntimeException if the client respond an error, contains the code & message from the response error.
     */
    private function create(): void
    {
        $token = WorkDoneToken::generate();
        /** @var ResponseMessage $response */
        $response = wait($this->api->create($token));

        if ($response->error) {
            throw new RuntimeException(
                $response->error->message,
                $response->error->code,
            );
        }

        $this->token = $token;
    }
}
