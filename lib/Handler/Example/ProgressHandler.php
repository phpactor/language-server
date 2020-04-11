<?php

namespace Phpactor\LanguageServer\Handler\Example;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

class ProgressHandler implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'example/progress' => 'progress'
        ];
    }

    public function progress(ServerClient $client)
    {
        return \Amp\call(function () use ($client) {
            yield $client->request('window/workDoneProgress/create', [
                'token' => $token,
            ]);
            $client->notification('$/progress', [
                'token' => $token,
                'value' => [
                    'kind' => 'begin',
                    'title' => 'Indexing',
                ],
            ]);
        });
    }
}
