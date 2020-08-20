<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionOptions;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use function Amp\call;

class CodeActionHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var CodeActionProvider
     */
    private $provider;

    /**
     * @var Workspace
     */
    private $workspace;


    public function __construct(CodeActionProvider $provider, Workspace $workspace)
    {
        $this->provider = $provider;
        $this->workspace = $workspace;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            CodeActionRequest::METHOD => 'codeAction'
        ];
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $options = new CodeActionOptions($this->provider->kinds());
        $capabilities->codeActionProvider = $options;
    }

    /**
     * @return Promise<array<CodeAction>>
     */
    public function codeAction(CodeActionParams $params): Promise
    {
        return call(function () use ($params) {
            $document = $this->workspace->get($params->textDocument->uri);
            return $this->provider->provideActionsFor($document, $params->range);
        });
    }
}
