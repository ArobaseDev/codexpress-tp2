<?php
namespace App\Service;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


class EmailNotificationService {
    public function __construct(private MailerInterface $mailer)
 {
    $this->mailer = $mailer;
}
public function sendEmail(string $receiver, string $case) :  ? string {
    try {
    //    $email = (new Email())  // Email sans template
        $email = (new TemplatedEmail())
        ->from('hello@codexpress.fr')
        ->to($receiver)
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        
        ->subject('Time for Symfony Mailer!')
        ->html('<p>See Twig integration for better HTML integration!</p>')
        ->htmlTemplate('email/base.html.twig');

        if($case === 'premium') {
            $email
                ->subject('Thanks for your purchase !')
                ->priority(Email::PRIORITY_HIGH)
                ->htmlTemplate('email/premium.html.twig'  );
        } elseif ($case === 'registration') {
            $email
                ->subject('Welcome to CodeXpress!')
                ->htmlTemplate('email/welcome.html.twig'  );
        }
    
    $this->mailer->send($email);
    } catch (\Exception $e) {
        return  'An error occured while sending the email: '.$e->getMessage();
    }
}

}