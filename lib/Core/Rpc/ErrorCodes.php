<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class ErrorCodes
{
    // defined by JSON RPC
    const ParseError = -32700;
    const InvalidRequest = -32600;
    const MethodNotFound = -32601;
    const InvalidParams = -32602;
    const InternalError = -32603;
    const serverErrorStart = -32099;
    const serverErrorEnd = -32000;
    const ServerNotInitialized = -32002;
    const UnknownErrorCode = -32001;

    // defined by LSP
    const RequestCancelled = -32800;
}
