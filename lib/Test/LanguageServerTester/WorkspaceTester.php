<?php

namespace Phpactor\LanguageServer\Test\LanguageServerTester;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\ExecuteCommandParams;
use Phpactor\LanguageServer\Test\LanguageServerTester;

class WorkspaceTester
{
    /**
     * @var LanguageServerTester
     */
    private $tester;

    public function __construct(LanguageServerTester $tester)
    {
        $this->tester = $tester;
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
