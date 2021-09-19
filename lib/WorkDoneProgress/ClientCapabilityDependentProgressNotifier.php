<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\Server\ClientApi;

final class ClientCapabilityDependentProgressNotifier implements ProgressNotifier
{
    /**
     * @var ProgressNotifier
     */
    private $notifier;

    public function __construct(ClientApi $api, ClientCapabilities $capabilities)
    {
        $this->notifier = $this->createNotifier($api, $capabilities);
    }

    /**
     * {@inheritDoc}
     */
    public function create(WorkDoneToken $token): Promise
    {
        return $this->notifier->create($token);
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
        $this->notifier->begin($token, $title, $message, $percentage, $cancellable);
    }

    /**
     * {@inheritDoc}
     */
    public function report(
        WorkDoneToken $token,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        $this->notifier->report($token, $message, $percentage, $cancellable);
    }

    public function end(WorkDoneToken $token, ?string $message = null): void
    {
        $this->notifier->end($token, $message);
    }

    private function createNotifier(ClientApi $api, ClientCapabilities $capabilities): ProgressNotifier
    {
        if ($capabilities->window['workDoneProgress'] ?? false) {
            return new WorkDoneProgressNotifier($api);
        }

        return new MessageProgressNotifier($api);
    }
}
