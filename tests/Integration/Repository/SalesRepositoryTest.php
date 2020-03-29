<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Integration\Repository;

use CViniciusSDias\RecargaTvExpress\Exception\NotEnoughCodesException;
use CViniciusSDias\RecargaTvExpress\Model\Code;
use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use CViniciusSDias\RecargaTvExpress\Repository\CodeRepository;
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use CViniciusSDias\RecargaTvExpress\Repository\SalesRepository;
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
        $codeRepository = $this->createCodeRepository();
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $sales = $salesRepository->salesWithCodes();

        self::assertIsArray($sales);
        self::assertCount(4, $sales);
        foreach ($sales as $sale) {
            self::assertInstanceOf(Code::class, $sale->code);
        }
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

    private function createCodeRepository()
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
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $salesRepository->salesWithCodes();
    }

    public function testWhenNotEnoughCodesAreFoundAnExceptionMustBeThrown()
    {
        $this->expectException(NotEnoughCodesException::class);
        $exceptionMessage = <<<MSG
        You don't have enough codes for all your sales.
        Number of annual sales: 2. Number of annual codes available: 2.
        Number of monthly sales: 2. Number of monthly codes available: 1.
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
            ]);
        $con = $this->createStub(\PDO::class);
        $salesRepository = new SalesRepository($emailSalesReader, $codeRepository, $con);

        $salesRepository->salesWithCodes();
    }
}