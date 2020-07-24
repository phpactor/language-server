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
     *             'textDocument/completion' => 'complete'
     *         ];
     *     }
     *
     *     public function didOpen(DidOpenTextDocumentParams $params): Promise
     *     {
     *         return new Success(null);
     *     }
     *
     *     public function complete(CompletionParams $params, CancellationToken $cancel): Promise
     *     {
     *         return new Success(new CompletionList(// ...));
     *     }
     * ```
     *
     * Each method MUST return an `Amp\Promise`.
     *
     * The arguments passed by the RPC call depend on the `ArgumentResolver`
     * implementation used by the `HandlerMethodRunner`.
     */
    public function methods(): array;
}
