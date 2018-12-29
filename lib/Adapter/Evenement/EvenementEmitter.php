<?php

namespace Phpactor\LanguageServer\Adapter\Evenement;

use Evenement\EventEmitterTrait;
use Phpactor\LanguageServer\Core\Event\EventEmitter;

class EvenementEmitter implements EventEmitter
{
    use EventEmitterTrait;
}
