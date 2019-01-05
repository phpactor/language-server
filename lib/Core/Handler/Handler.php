<?php

namespace Phpactor\LanguageServer\Core\Handler;

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
     *     public function didOpen(): Generator
     *     {
     *         yield new SomeRespose();
     *     }
     * ```
     */
    public function methods(): array;
}
