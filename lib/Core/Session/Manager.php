<?php

namespace Phpactor\LanguageServer\Core\Session;

use RuntimeException;

class Manager
{
    /**
     * @var Session|null
     */
    private $session;

    public function initialize(string $rootUri, int $processId = null)
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
