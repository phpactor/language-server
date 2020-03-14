<?php

namespace Phpactor\LanguageServer\Core\Handler;

interface ServiceProvider extends Handler
{
    /**
     * Return a map of service names to instance method names.
     *
     * Services are asynchronous co-routines will be started when the
     * language server is initialized.
     */
    public function services(): array;
}
