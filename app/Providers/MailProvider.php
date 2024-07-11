<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use PHPMailer\PHPMailer\PHPMailer;

class MailProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['mail', PHPMailer::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(PHPMailer::class, function () {
            $env = $this->getContainer()->get('env');

            $mail = new PHPMailer();
            $mail->setLanguage('zh_cn');
            //Server settings
            call_user_func([$mail, 'is' . $env->get('MAIL_MAILER', 'smtp')]);
            $mail->Host       = $env->get('MAIL_HOST', 'smtp.mailtrap.io');                 //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $env->get('MAIL_USERNAME');             //SMTP username
            $mail->Password   = $env->get('MAIL_PASSWORD');             //SMTP password
            $mail->SMTPSecure = $env->get('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS);            //Enable implicit TLS encryption
            $mail->Port       = $env->get('MAIL_PORT', 2525);                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            //Recipients
            $mail->setFrom($env->get('MAIL_FROM_ADDRESS'), $env->get('MAIL_FROM_NAME'));
            return $mail;
        })->setAlias('mail');
    }
}
