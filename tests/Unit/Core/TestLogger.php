<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    private $messages = [];

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->messages[] = sprintf('[%s] %s %s', $level, $message, json_encode($context));
    }

    public function messages(): array
    {
        return $this->messages;
    }
}
