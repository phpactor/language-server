<?php

namespace Phpactor\LanguageServer\Test\LanguageServerTester;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\ExecuteCommandParams;
use Phpactor\LanguageServer\Test\LanguageServerTester;

class WorkspaceTester
{
    public function __construct(private LanguageServerTester $tester)
    {
    }

    /**
     * @return Promise<mixed>
     */
    public function executeCommand(string $command, ?array $args = []): Promise
    {
        return $this->tester->request(
            'workspace/executeCommand',
            new ExecuteCommandParams(
                $command,
                $args
            )
        );
    }
}
