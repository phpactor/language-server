<?php

namespace Phpactor\LanguageServer\Handler\Example;

use Amp\Delayed;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Ramsey\Uuid\Uuid;

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
    public function progress(RpcClient $client): Promise
    {
        return \Amp\call(function () use ($client) {
            $token = Uuid::uuid4();
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

            for ($progress = 0; $progress <= 100; $progress++) {
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
