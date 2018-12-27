<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Serializer;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Exception\RequestError;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;

class JsonSerializerTest extends TestCase
{
    /**
     * @var JsonSerializer
     */
    private $serializer;

    public function setUp()
    {
        $this->serializer = new JsonSerializer();
    }

    public function testRequestErrorForDeserializeInvalidJson()
    {
        $this->expectException(RequestError::class);
        $this->serializer->deserialize('asdf');
    }

    public function testDeserializesJsonToArray()
    {
        $array = $this->serializer->deserialize('{"foo":"bar"}');
        $this->assertEquals(['foo' => 'bar'], $array);
    }

    public function testRequestErrorForSerializeInvalidPayload()
    {
        $this->expectException(RequestError::class);
        $payload = fopen('php://temporary', 'w');
        $this->serializer->serialize([$payload]);
        fclose($payload);
    }

    public function testSerializesArrayPayloadToString()
    {
        $string = $this->serializer->serialize(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $string);
    }
}
