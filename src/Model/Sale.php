<?php

namespace CViniciusSDias\RecargaTvExpress\Model;

use CViniciusSDias\RecargaTvExpress\Model\VO\Email;

/**
 * @property-read Email $costumerEmail
 * @property-read string $product
 * @property-read Code $code
 */
class Sale
{
    use PropertyAccess;

    private $costumerEmail;
    private $product;
    private $code;

    public function __construct(Email $costumerEmail, string $product)
    {
        $this->costumerEmail = $costumerEmail;
        $this->setProduct($product);
    }

    private function setProduct(string $product): void
    {
        $productName = trim($product);
        if (!in_array($productName, ['anual', 'mensal', 'anual-mc', 'mensal-mc'])) {
            throw new \DomainException("$productName is not a valid product");
        }

        $this->product = $productName;
    }

    public function attachCode(Code $code): void
    {
        $this->code = $code;
    }
}
