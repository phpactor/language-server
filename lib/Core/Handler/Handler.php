<?php

namespace Phpactor\LanguageServer\Core\Handler;

/**
 * Language Server Handler
 *
 * See documentation for the `methods` method for more details.
 */
interface Handler
{
    /**
     * Return a map of RPC method names to instance method names.
     *
     * For example:
     *
     * ```
     *     public function methods(): array
     *     {
     *         return [
     *             'textDocument/didOpen' => 'didOpen'
     *         ];
     *     }
     *
     *     public function didOpen(TextDocumentItem $item): Promise
     *     {
     *         yield new SomeRespose();
     *     }
     * ```
     *
     * Each method MUST return an `Amp\Promise`.
     *
     * The arguments passed by the RPC call are automatically resolved.
     *
     * LSP params
     * ----------
     *
     * If you implement the LSP `*Params` object, e.g. `initialize(InitializeParams $params)`
     * then the RPC arguments will be coerced into a `Phpactor\LanguageServerProtocol\InitializeParams` object.
     *
     * You can also have a second parameter with hte `Amp\CancellationToken` which will
     * allow you to cancel your subrouting.
     *
     * RPC params
     * ----------
     *
     * If no LSP parameter type is defined, then the arguments will be mapped directly to
     * your parameters.
     */
    public function methods(): array;
}
