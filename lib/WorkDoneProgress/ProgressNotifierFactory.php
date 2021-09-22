<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use RuntimeException;

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

    public function create(?WorkDoneToken $token = null): ProgressNotifier
    {
        if (!$token && !$this->canServerInitiateProgress()) {
            return new MessageProgressNotifier($this->api);
        }

        try {
            return new WorkDoneProgressNotifier($this->api, $token);
        } catch (RuntimeException $error) {
            if (ErrorCodes::MethodNotFound === $error->getCode()) {
                return new MessageProgressNotifier($this->api);
            }

            throw $error;
        }
    }

    private function canServerInitiateProgress(): bool
    {
        return $this->capabilities->window['workDoneProgress'] ?? false;
    }
}
