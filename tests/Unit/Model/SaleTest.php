<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Model;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use PHPUnit\Framework\TestCase;

class SaleTest extends TestCase
{
    public function testCreatingASaleWithAnInvalidProductMustThrowException()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('invalid is not a valid product');

        new Sale(new Email('email@example.com'), 'invalid');
    }

    /**
     * @dataProvider productTypes
     * @param string $productType
     */
    public function testCreatingASaleWithAValidProductMustWork(string $productType)
    {
        $sale = new Sale(new Email('email@example.com'), $productType);

        self::assertSame($productType, $sale->product);
    }

    public function productTypes(): array
    {
        return [
            ['anual'],
            ['anual-mc'],
            ['mensal'],
            ['mensal-mc'],
        ];
    }
}
