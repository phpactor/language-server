<?php

namespace Phpactor\LanguageServer\Core\Session\DispatcherFactory;

use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Server\SessionServices;
use Phpactor\LanguageServer\Core\Session\DispatcherFactory;

class DefaultDispatcherFactory implements DispatcherFactory
{
    public function create(SessionServices $services): Dispatcher
    {
    }
}
