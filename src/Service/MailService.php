<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private MailerInterface $mailer;

    /**
     * Constructeur.
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Service d'envoie de mail a partir de la MailerInterface.
     *
     * @throws TransportExceptionInterface
     */
    public function sendEmail(
        string $from,
        string $subject,
        array $context
    ): void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to('contact.enisortir@gmail.com')
            ->subject($subject)
            ->htmlTemplate('utils/email.html.twig')
            ->context($context);
        $this->mailer->send($email);
    }
}
