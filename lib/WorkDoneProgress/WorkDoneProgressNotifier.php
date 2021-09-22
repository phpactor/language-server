<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\Client\WorkDoneProgressClient;
use RuntimeException;
use Throwable;

final class WorkDoneProgressNotifier implements ProgressNotifier
{
    /**
     * @var WorkDoneProgressClient
     */
    private $api;

    /**
     * @var MessageProgressNotifier
     */
    private $fallbackApi;

    /**
     * @var Promise<WorkDoneToken|null>
     */
    private $promise;

    public function __construct(ClientApi $api, ?string $token = null)
    {
        $this->api = $api->workDoneProgress();
        $this->fallbackApi = new MessageProgressNotifier($api);
        $this->promise = $token ? new Success(new WorkDoneToken($token)) : $this->create();
    }

    /**
     * {@inheritDoc}
     */
    public function begin(
        string $title,
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        $this->onResolve(function (WorkDoneToken $token, ...$args): void {
            $this->api->begin($token, ...$args);
        }, function (...$args): void {
            $this->fallbackApi->begin(...$args);
        }, $title, $message, $percentage, $cancellable);
    }

    /**
     * {@inheritDoc}
     */
    public function report(
        ?string $message = null,
        ?int $percentage = null,
        ?bool $cancellable = null
    ): void {
        $this->onResolve(function (WorkDoneToken $token, ...$args): void {
            $this->api->report($token, ...$args);
        }, function (...$args): void {
            $this->fallbackApi->report(...$args);
        }, $message, $percentage, $cancellable);
    }

    public function end(?string $message = null): void
    {
        $this->onResolve(function (WorkDoneToken $token, ...$args): void {
            $this->api->end($token, ...$args);
            $this->promise = new Success(null);
        }, function (...$args): void {
            $this->fallbackApi->end(...$args);
            $this->promise = new Success(null);
        }, $message);
    }

    /**
     * @return Promise<WorkDoneToken>
     */
    private function create(): Promise
    {
        return \Amp\call(function () {
            $token = WorkDoneToken::generate();
            $response = yield $this->api->create($token);
            assert($response instanceof ResponseMessage);

            if ($error = $response->error) {
                throw new RuntimeException($error->message, $error->code);
            }

            return $token;
        });
    }

    /**
     * @param mixed[] $args
     */
    private function onResolve(callable $onSucess, callable $onError, ...$args): void
    {
        \Amp\asyncCall(function (callable $onSuccess, callable $onError, ...$args) {
            try {
                if (!$token = yield $this->promise) {
                    return; // Stop if no more token (end notification was sent)
                }
                assert($token instanceof WorkDoneToken);

                $onSuccess($token, ...$args);
            } catch (Throwable $e) {
                $onError(...$args);
            }
        }, $onSucess, $onError, ...$args);
    }
}
