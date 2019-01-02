<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Socket\ClientSocket;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\LanguageServerBuilder;
use RuntimeException;

class ServerTester
{
    /**
     * @var LanguageServer
     */
    private $server;

    public function __construct(LanguageServerBuilder $builder = null)
    {
        $builder->eventLoop(false);
        $this->server = $builder->build();
        $this->server->start();
    }

    public function dispatch(string $method, array $params = []): array
    {
        /** @var ClientSocket $client */
        $client = \Amp\Promise\wait(\Amp\Socket\connect($this->server->address()));
        $request = new RequestMessage(1, $method, $params);
        $writer = new LanguageServerProtocolWriter();

        \Amp\Promise\wait($client->write($writer->write($request)));

        $rawResponse = \Amp\Promise\wait($client->read());
        $parser = (new LanguageServerProtocolParser())->__invoke();

        $responses = [];
        while ($response = $parser->send($rawResponse)) {
            $responses[] = $response;
            $rawResponse = null;
        }

        return $responses;
    }

    public function initialize()
    {
        $responses = $this->dispatch('initialize', [
            'rootUri' => __DIR__,
        ]);
        $this->assertSuccess($responses);
    }

    public function assertSuccess($responses): bool
    {
        $responses = (array) $responses;

        /** @var Request $response */
        foreach ($responses as $response) {
            if ($response->body()['responseError']) {
                throw new RuntimeException(sprintf(
                    'Response contains error: %s',
                    json_encode($response->body()['responseError'], JSON_PRETTY_PRINT)
                ));
            }
        }

        return true;
    }
}
