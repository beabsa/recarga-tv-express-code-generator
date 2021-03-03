<?php

namespace CViniciusSDias\RecargaTvExpress\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CodesCountWarningSender
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWarning(array $codeCount): void
    {
        $emailBody = "Códigos disponíveis:\n";
        foreach ($codeCount as $product => $count) {
            $emailBody .= "$product: $count\n";
        }

        $email = (new Email())
            ->from('wolneidias@gmail.com')
            ->to('wolneidias@gmail.com')
            ->subject('Códigos acabando!')
            ->priority(1)
            ->text($emailBody);
        $this->mailer->send($email);
    }
}
