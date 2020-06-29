<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

use Amp\CancellationToken;
use DTL\Invoke\Invoke;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;
use ReflectionClass;
use ReflectionType;
use Symfony\Component\Process\Exception\RuntimeException;

class LanguageSeverProtocolParamsResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolveArguments(object $object, string $method, array $arguments, array $extraArgs): array
    {
        $reflection = new ReflectionClass($object);

        if (!$reflection->hasMethod($method)) {
            throw new CouldNotResolveArguments(sprintf(
                'Class "%s" has no method "%s"',
                get_class($object),
                $method
            ));
        }

        $cancellationToken = $this->resolveCancellationToken($extraArgs);

        foreach ($reflection->getMethod($method)->getParameters() as $parameter) {
            if (!$parameter->getType()) {
                continue;
            }

            $type = $parameter->getType();

            /** @var class-string */
            $classFqn = $type->__toString();

            if (preg_match('{^Phpactor\\\LanguageServerProtocol\\\.*Params$}', $classFqn)) {
                return $this->doResolveArguments($classFqn, $parameter->getName(), $arguments, $extraArgs, $cancellationToken);
            }

            throw new CouldNotResolveArguments(sprintf(
                'First argument of LSP class "%s" method "%s" must be the LSP param object',
                get_class($object),
                $method
            ));
        }

        throw new CouldNotResolveArguments(sprintf(
            'Class "%s" method "%s" is not a language server protocol-accepting method',
            get_class($object),
            $method
        ));
    }

    /**
     * @param class-string $classFqn
     */
    private function doResolveArguments(string $classFqn, string $paramName, array $arguments, array $extraArgs, ?CancellationToken $cancellationToken): array
    {
        $reflection = new ReflectionClass($classFqn);

        $args = [
            $reflection->getMethod('fromArray')->invoke(null, $arguments, true)
        ];
        
        if ($cancellationToken) {
            $args[] = $cancellationToken;
        }

        return $args;
    }

    private function resolveCancellationToken(array $extraArgs): ?CancellationToken
    {
        if (!$extraArgs) {
            return null;
        }

        if (count($extraArgs) > 1) {
            throw new RuntimeException(
                'LSP protocol parameter has > 1 extra argument, but only 1 argument (CancallationToken) is permitted',
            );
        }

        $arg = reset($extraArgs);

        if (!$arg instanceof CancellationToken) {
            throw new RuntimeException(sprintf(
                'Extra argument for LSP method must be cancellation token, got "%s"',
                is_object($arg) ? get_class($arg) : gettype($arg)
            ));
        }

        return $arg;
    }
}
