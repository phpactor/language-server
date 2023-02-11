<?php

namespace Phpactor\LanguageServer\Test\LanguageServerTester;

use Phpactor\LanguageServer\Test\LanguageServerTester;

class ServicesTester
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
     * Return running services
     *
     * @return list<string>
     */
    public function listRunning(): array
    {
        $response = $this->tester->mustRequestAndWait('phpactor/service/running', []);
        $running = $response->result;
        if (!is_array($running)) {
            return [];
        }
        return $running;
    }

    /**
     * Stop the named service
     */
    public function stop(string $name): void
    {
        $this->tester->notifyAndWait('phpactor/service/stop', ['name' => $name]);
    }

    /**
     * Start the named service
     */
    public function start(string $name): void
    {
        $this->tester->notifyAndWait('phpactor/service/start', ['name' => $name]);
    }
}
