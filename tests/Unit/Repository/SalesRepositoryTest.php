<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Repository;

use CViniciusSDias\RecargaTvExpress\Exception\NotEnoughCodesException;
use CViniciusSDias\RecargaTvExpress\Model\Code;
use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use CViniciusSDias\RecargaTvExpress\Repository\CodeRepository;
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use CViniciusSDias\RecargaTvExpress\Repository\SalesRepository;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Test class for integration between SalesRepository and it's dependencies, making sure it works if others work, and
 * throws exceptions when others throw exception. Also makes sure Code and Sale classes are working properly
 */
class SalesRepositoryTest extends TestCase
{
    public function testMustReturnCorrectlyParsedSalesWithTheirCodes()
    {
        $emailSalesReader = $this->createEmailSalesReader();
        $con = $this->createStub(\PDO::class);
        /** @var CodeRepository $codeRepository */
        $codeRepository = $this->createCodeRepository();
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $sales = $salesRepository->salesWithCodes();

        self::assertIsArray($sales);
        self::assertCount(4, $sales);
        foreach ($sales as $sale) {
            self::assertInstanceOf(Code::class, $sale->code);
        }
        self::assertSame('anual', $sales[0]->product);
        self::assertSame('1111', $sales[0]->code->serial);
        self::assertSame('anual', $sales[1]->product);
        self::assertSame('2222', $sales[1]->code->serial);
        self::assertSame('mensal', $sales[2]->product);
        self::assertSame('3333', $sales[2]->code->serial);
        self::assertSame('mensal', $sales[3]->product);
        self::assertSame('4444', $sales[3]->code->serial);
    }

    private function createEmailSalesReader(): EmailSalesReader
    {
        $emailSalesReader = $this->createStub(EmailSalesReader::class);
        $emailSalesReader
            ->method('findSales')
            ->willReturn([
                new Sale(new Email('email@example.com'), 'mensal'),
                new Sale(new Email('email@example.com'), 'anual'),
                new Sale(new Email('email@example.com'), 'mensal'),
                new Sale(new Email('email@example.com'), 'anual'),
            ]);

        return $emailSalesReader;
    }

    private function createCodeRepository(): Stub
    {
        $codeRepository = $this->createStub(CodeRepository::class);
        $codeRepository->method('findUnusedCodes')
            ->willReturn([
                'anual' => [
                    new Code(1, '1111', new Email('email@example.com')),
                    new Code(2, '2222', new Email('email@example.com')),
                ],
                'mensal' => [
                    new Code(3, '3333', new Email('email@example.com')),
                    new Code(4, '4444', new Email('email@example.com')),
                ],
                'anual-mc' => [],
                'mensal-mc' => [],
            ]);

        return $codeRepository;
    }

    public function testFailureOnExecuteQueryMustRollbackTransactionAndThrowException()
    {
        $this->expectException(\PDOException::class);

        $emailSalesReader = $this->createEmailSalesReader();
        $codeRepository = $this->createCodeRepository();
        $codeRepository
            ->method('attachCodeToSale')
            ->willThrowException(new \PDOException());
        $con = $this->createStub(\PDO::class);
        /** @var CodeRepository $codeRepository */
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $salesRepository->salesWithCodes();
    }

    public function testWhenNotEnoughCodesAreFoundAnExceptionMustBeThrown()
    {
        $this->expectException(NotEnoughCodesException::class);
        $exceptionMessage = <<<MSG
        Você não possui códigos suficientes para todas as vendas.
        
        Número de vendas anual-mc: 0. Número de códigos anual-mc disponíveis: 0
        Número de vendas mensal-mc: 0. Número de códigos mensal-mc disponíveis: 0
        Número de vendas anual: 2. Número de códigos anual disponíveis: 2
        Número de vendas mensal: 2. Número de códigos mensal disponíveis: 1
        MSG;
        $this->expectExceptionMessage($exceptionMessage);

        $emailSalesReader = $this->createEmailSalesReader();
        $codeRepository = $this->createStub(CodeRepository::class);
        $codeRepository->method('findUnusedCodes')
            ->willReturn([
                'anual' => [
                    new Code(1, '1111', new Email('email@example.com')),
                    new Code(2, '2222', new Email('email@example.com')),
                ],
                'mensal' => [
                    new Code(3, '3333', new Email('email@example.com')),
                ],
                'anual-mc' => [],
                'mensal-mc' => [],
            ]);
        $con = $this->createStub(\PDO::class);
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $salesRepository->salesWithCodes();
    }
}
