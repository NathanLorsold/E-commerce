<?php

namespace App\Service;

use \private;
use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendOrderConfirmation(string $to, string $subject, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from('noreply@monsite.com') //On peut modifier cette ligne si besoin
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('emails/order_confirmation.html.twig') // chemin vers ton template Twig
            ->context($context);

        $this->mailer->send($email);
    }
}
