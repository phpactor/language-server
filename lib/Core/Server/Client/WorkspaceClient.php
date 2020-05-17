<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Amp\Promise;
use DTL\Invoke\Invoke;
use LanguageServerProtocol\ApplyWorkspaceEditResponse;
use LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Server\RpcClient;

class WorkspaceClient
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return Promise<ApplyWorkspaceEditResponse>
     */
    public function applyEdit(WorkspaceEdit $edit, ?string $label = null): Promise
    {
        return \Amp\call(function () use ($edit, $label) {
            $response = yield $this->client->request(
                'workspace/applyEdit',
                [
                    'edit' => $edit,
                    'label' => $label
                ]
            );

            return Invoke::new(ApplyWorkspaceEditResponse::class, (array)$response->result);
        });
    }
}
