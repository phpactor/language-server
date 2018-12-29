<?php

namespace Phpactor\LanguageServer\Core\Event;

interface LanguageServerEvents
{
    const CAPABILITIES_REGISTER = 'register_capabilities';
    const TEXT_DOCUMENT_OPENED = 'text_document.opened';
    const TEXT_DOCUMENT_UPDATED = 'text_document.updated';
    const TEXT_DOCUMENT_CLOSED = 'text_document.closed';
    const TEXT_DOCUMENT_WILL_SAVE  = 'text_document.will_save';
    const TEXT_DOCUMENT_WILL_SAVE_WAIT_UNTIL  = 'text_document.will_save_wait_until';
}