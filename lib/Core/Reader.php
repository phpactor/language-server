<?php

namespace Phpactor\LanguageServer\Core;

interface Reader
{
    public function readRequest(IO $io): string;
}
