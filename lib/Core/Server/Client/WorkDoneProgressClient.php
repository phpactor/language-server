<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Amp\Promise;
use InvalidArgumentException;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;

final class WorkDoneProgressClient
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
     * @return Promise<ResponseMessage>
     */
    public function create(WorkDoneToken $token): Promise
    {
        return \Amp\call(function () use ($token) {
            return yield $this->client->request('window/workDoneProgress/create', [
                'token' => (string) $token,
            ]);
        });
    }

    public function begin(
        WorkDoneToken $token,
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        self::assertIsValidPercentage($percentage);

        $this->notify($token, [
            'kind' => 'begin',
            'title' => $title,
            'message' => $message,
            'percentage' => $percentage,
            'cancellable' => $cancellable,
        ]);
    }

    public function report(
        WorkDoneToken $token,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        self::assertIsValidPercentage($percentage);

        $this->notify($token, [
            'kind' => 'report',
            'message' => $message,
            'percentage' => $percentage,
            'cancellable' => $cancellable,
        ]);
    }

    public function end(WorkDoneToken $token, ?string $message = null): void
    {
        $this->notify($token, [
            'kind' => 'end',
            'message' => $message,
        ]);
    }

    private static function assertIsValidPercentage(?int $percentage): void
    {
        if (!(null === $percentage || 0 <= $percentage && $percentage <= 100)) {
            throw new InvalidArgumentException(
                'The percentage must be an integer comprised between 0 and 100.',
            );
        }
    }

    private function notify(WorkDoneToken $token, array $value): void
    {
        assert(in_array($value['kind'], ['begin', 'report', 'end']));

        $this->client->notification('$/progress', [
            'token' => (string) $token,
            'value' => $value,
        ]);
    }
}
