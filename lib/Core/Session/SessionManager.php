<?php

namespace Phpactor\LanguageServer\Core\Session;

use RuntimeException;

class SessionManager
{
    /**
     * @var Session|null
     */
    private $session;

    public function load(Session $session)
    {
        $this->session = $session;
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
