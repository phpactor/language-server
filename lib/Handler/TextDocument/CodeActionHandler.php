<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionOptions;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use function Amp\call;

class CodeActionHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(private CodeActionProvider $provider, private Workspace $workspace, private ClientApi $client)
    {
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
    public function codeAction(CodeActionParams $params, CancellationToken $cancel): Promise
    {
        return call(function () use ($params, $cancel) {
            $token = WorkDoneToken::generate();
            $this->client->workDoneProgress()->create($token);
            $document = $this->workspace->get($params->textDocument->uri);
            $this->client->workDoneProgress()->begin($token, title: 'Resolving code actions');
            $actions = yield $this->provider->provideActionsFor($document, $params->range, $cancel);
            $this->client->workDoneProgress()->end($token);
            return $actions;
        });
    }
}
