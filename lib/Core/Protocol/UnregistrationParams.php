<?php

namespace Phpactor\LanguageServer\Core\Protocol;

class UnregistrationParams
{
    /**
     * @var Unregistration[]
     */
    private $unregistrations;

    /**
     * @param Unregistration[] $unregistrations
     */
    public function __construct(array $unregistrations  = [])
    {
        $this->unregistrations = $unregistrations;
    }
}
