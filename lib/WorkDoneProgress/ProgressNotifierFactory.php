<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\Server\ClientApi;

final class ProgressNotifierFactory
{
    /**
     * @var ClientApi
     */
    private $api;

    /**
     * @var ClientCapabilities
     */
    private $capabilities;

    public function __construct(ClientApi $api, ClientCapabilities $capabilities)
    {
        $this->api = $api;
        $this->capabilities = $capabilities;
    }

    public function create(?string $token = null): ProgressNotifier
    {
        if (!$token && !$this->canServerInitiateProgress()) {
            return new MessageProgressNotifier($this->api);
        }

        return new WorkDoneProgressNotifier($this->api, $token);
    }

    private function canServerInitiateProgress(): bool
    {
        return $this->capabilities->window['workDoneProgress'] ?? false;
    }
}
