<?php

namespace Phpactor\LanguageServer\Core\Handler\Session;

use LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

class Status implements Handler
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Manager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'session/status';
    }

    public function __invoke(string $buftype, string $languageId)
    {
        $session = $this->sessionManager->current();

        yield null;
        yield new NotificationMessage('window/showMessage', [
            'type' => MessageType::INFO,
            'message' => implode(', ', [
                'up: ' . $session->uptime()->format('%ad %hh %im %ss'),
                'mem: ' . number_format(memory_get_peak_usage()) . 'b',
                'files: ' . $session->workspace()->openFiles()
            ]),
        ]);
    }
}
