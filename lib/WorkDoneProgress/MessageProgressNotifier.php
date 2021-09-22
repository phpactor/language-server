<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\Client\MessageClient;

final class MessageProgressNotifier implements ProgressNotifier
{
    /**
     * @var MessageClient
     */
    private $api;

    /**
     * @var string The title of the progress being reported
     */
    private $title;

    public function __construct(ClientApi $api)
    {
        $this->api = $api->window()->showMessage();
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
        $this->title = $title;

        $this->api->info(
            $this->formatMessage($message, $percentage),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function report(?string $message = null, ?int $percentage = null, ?bool $cancellable = null): void
    {
        $this->api->info(
            $this->formatMessage($message, $percentage),
        );
    }

    public function end(?string $message = null): void
    {
        $this->api->info(
            $this->formatMessage($message),
        );
    }

    private function formatMessage(?string $message, ?int $percentage = null): string
    {
        $progress = [$this->title];
        
        if ($message) {
            $progress[] = sprintf(': %s', $message);
        }
        
        if ($percentage) {
            $progress[] = $message ? ', ' : ': ';
            $progress[] = sprintf('%d%% done', $percentage);
        }
        
        return implode('', $progress);
    }
}
