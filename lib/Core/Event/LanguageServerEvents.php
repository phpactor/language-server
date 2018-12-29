<?php

namespace Phpactor\LanguageServer\Core\Event;

interface LanguageServerEvents
{
    const CAPABILITIES_REGISTER = 'register_capabilities';
    const TEXT_DOCUMENT_OPENED = 'text_document.opened';
    const TEXT_DOCUMENT_UPDATED = 'text_document.updated';
    const TEXT_DOCUMENT_CLOSED = 'text_document.closed';

}
