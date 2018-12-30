<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;

class SystemHandler implements Handler
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function methods(): array
    {
        return [
            'system/status' => 'status',
        ];
    }

    public function status()
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
