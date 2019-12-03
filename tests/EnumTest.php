<?php

declare(strict_types=1);

namespace Idiosyncratic;

use DomainException;
use ErrorException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EnumTest extends TestCase
{
    public function testEquals() : void
    {
        $fruit1 = Stub\Fruit::apple();
        $fruit2 = Stub\Fruit::apple();

        $this->assertTrue($fruit1->equals($fruit2));
    }

    public function testJsonSerialize() : void
    {
        $fruit = Stub\Fruit::apple();

        $this->assertEquals(sprintf('"%s"', $fruit->value()), json_encode($fruit));

        $number = Stub\Number::one();

        $this->assertEquals($number->value(), json_encode($number));
    }

    public function testInvalidValue() :void
    {
        $this->expectException(DomainException::class);

        Stub\Fruit::create('carrot');
    }

    public function testInvalidMagicFactory() :void
    {
        $this->expectException(ErrorException::class);

        Stub\Fruit::CARROT();
    }

    public function testStringConversion() :void
    {
        $this->assertEquals((string) 1, (string) Stub\Number::ONE());
    }
}
