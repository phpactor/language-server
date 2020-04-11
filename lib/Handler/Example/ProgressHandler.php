<?php

namespace Phpactor\LanguageServer\Handler\Example;

use Amp\Delayed;
use Amp\Promise;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\ServerClient;

class ProgressHandler implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'example/progress' => 'progress',
        ];
    }

    /**
     * @return Promise<null>
     */
    public function progress(ServerClient $client): Promise
    {
        return \Amp\call(function () use ($client) {
            $token = uniqid();
            yield $client->request('window/workDoneProgress/create', [
                'token' => $token,
            ]);

            $client->notification('$/progress', [
                'token' => $token,
                'value' => [
                    'kind' => 'begin',
                    'title' => 'Indexing',
                    'percentage' => 0,
                ],
            ]);

            for($progress = 0; $progress <= 100; $progress++) {
                $client->notification('$/progress', [
                    'token' => $token,
                    'value' => [
                        'kind' => 'report',
                        'percentage' => $progress
                    ],
                ]);
                yield new Delayed(100);
            }

            return null;
        });
    }
}
