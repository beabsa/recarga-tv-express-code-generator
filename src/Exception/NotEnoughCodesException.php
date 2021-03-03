<?php

namespace CViniciusSDias\RecargaTvExpress\Exception;

class NotEnoughCodesException extends \Exception
{
    public function __construct(
        int ...$numberOfSales
    ) {
        $format = <<<EOL
        Você não possui códigos suficientes para todas as vendas.
        
        Número de vendas anual-mc: %d. Número de códigos anual-mc disponíveis: %d
        Número de vendas mensal-mc: %d. Número de códigos mensal-mc disponíveis: %d
        Número de vendas anual: %d. Número de códigos anual disponíveis: %d
        Número de vendas mensal: %d. Número de códigos mensal disponíveis: %d
        EOL;

        $message = sprintf($format, ...$numberOfSales);

        parent::__construct(
            $message
        );
    }
}
