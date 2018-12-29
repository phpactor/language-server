<?php

namespace Phpactor\LanguageServer\Core\Protocol;

class RegistrationParams
{
    /**
     * @var array
     */
    public $registrations;

    /**
     * @param Registration[] $registrations
     */
    public function __construct(array $registrations = [])
    {
        $this->registrations = $registrations;
    }
}
