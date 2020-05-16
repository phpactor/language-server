<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\Handler;

class CommandHandler implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'workspace/executeCommand' => 'executeCommand',
        ];
    }

    /**
     * @return Promise<mixed|null>
     */
    public function executeCommand(): Promise
    {
        return \Amp\call(function () {
        });
    }
}
