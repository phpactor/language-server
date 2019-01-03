<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class Application
{
    private $initialized;

    /**
     * @var HandlerLoader
     */
    private $loader;

    public function __construct(HandlerLoader $loader)
    {
        $this->loader = $loader;
    }

    public function dispatch(RequestMessage $request)
    {
        if ($request->method === 'initialize') {
            $this->initialize($request);
        }
    }
}
