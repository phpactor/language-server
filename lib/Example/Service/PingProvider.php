<?php

namespace Phpactor\LanguageServer\Example\Service;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;

/**
 * Example service which shows a "ping" message every second.
 */
class PingProvider implements ServiceProvider
{
    public function __construct(private ClientApi $client)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping'
        ];
    }

    /**
     * @return Promise<null>
     */
    public function ping(CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($cancel) {
            while (true) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    break;
                }
                yield new Delayed(1000);
                $this->client->window()->showMessage()->info('ping');
            }
        });
    }
}
