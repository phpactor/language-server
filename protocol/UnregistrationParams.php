<?php

namespace LanguageServerProtocol;

class UnregistrationParams
{
    /**
     * @var Unregistration[]
     */
    public $unregistrations;

    /**
     * @param Unregistration[] $unregistrations
     */
    public function __construct(array $unregistrations  = [])
    {
        $this->unregistrations = $unregistrations;
    }
}
