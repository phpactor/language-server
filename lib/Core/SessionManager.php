<?php

namespace Phpactor\LanguageServer\Core;

class SessionManager
{
    /**
     * @var Session
     */
    private $session;

    public function initialize(string $rootUri, int $processId)
    {
        $this->session = new Session($rootUri, $processId);
    }

    public function current(): Session
    {
        if (!$this->session) {
            throw new RuntimeException(
                'Cannot get session before initialization'
            );
        }

        return $this->session;
    }
}
