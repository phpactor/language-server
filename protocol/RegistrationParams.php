<?php

namespace LanguageServerProtocol;

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
