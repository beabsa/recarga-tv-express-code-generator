<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Integration\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\EmailParser\{EmailParser, WixEmailParser};
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for integration between SalesFinder and EmailFinders
 */
class SalesFinderTest extends TestCase
{
    public function testSalesFinderShouldReturnEmptyArrayWhenNoParsableEmailIsFound()
    {
        $incomingMail = $this->createStub(IncomingMail::class);
        $incomingMail->fromAddress = 'info@mercadopago.com';
        $invalidEmailSubject = 'Você recebeu um pagamento por Combo MFC + TVE anual';
        $incomingMail->subject = $invalidEmailSubject;

        $mailbox = $this->createStub(Mailbox::class);
        $mailbox->method('searchMailbox')->willReturn([1]);
        $mailbox->method('getMail')->willReturn($incomingMail);

        $salesFinder = new EmailSalesReader($mailbox, $this->emailParser());

        $sales = $salesFinder->findSales();

        $this->assertEmpty($sales);
    }

    /**
     * @todo Implement tests for WixEmailParser
     */
    public function testSalesFinderShouldOnlyReturnSalesFromParsableEmails()
    {
        // arrange

        // invalid e-mail
        $incomingMailMock3 = $this->createStub(IncomingMail::class);
        $incomingMailMock3->fromAddress = 'wrong-email@example.com';
        $incomingMailMock3->subject = 'Você recebeu um pagamento por Combo MFC + TVE anual';

        // valid wix e-mail
        $incomingMailMock4 = $this->createStub(IncomingMail::class);
        $incomingMailMock4->subject = 'ÓTIMO! VOCÊ ACABOU DE RECEBER UM PEDIDO (#10001)';
        $incomingMailMock4->fromAddress = 'no-reply@mystore.wix.com';
        $incomingMailMock4->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-from-wix.html'));

        // valid wix e-mail with 3 sales
        $incomingMailMock5 = $this->createStub(IncomingMail::class);
        $incomingMailMock5->subject = 'ÓTIMO! VOCÊ ACABOU DE RECEBER UM PEDIDO (#10001)';
        $incomingMailMock5->fromAddress = 'no-reply@mystore.wix.com';
        $incomingMailMock5->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-from-wix-with-three-sales.html'));

        $mailbox = $this->createStub(Mailbox::class);
        $mailbox->method('searchMailbox')->willReturn([1, 2, 3]);
        $mailbox->method('getMail')->willReturnOnConsecutiveCalls(
            $incomingMailMock3,
            $incomingMailMock4,
            $incomingMailMock5,
        );

        $salesFinder = new EmailSalesReader($mailbox, $this->emailParser());

        // act
        $sales = $salesFinder->findSales();

        // assert
        $this->assertCount(4, $sales);
        $this->assertContainsOnlyInstancesOf(Sale::class, $sales);
    }

    private function emailParser(): EmailParser
    {
        $nullParser = new class extends EmailParser
        {
            protected function parseEmail(IncomingMail $email): array
            {
                return [];
            }

            protected function canParse(IncomingMail $email): bool
            {
                return true;
            }
        };

        return new WixEmailParser($nullParser);
    }
}
