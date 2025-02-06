<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SendMail
{
  private PHPMailer $mailSender;
  public function __construct()
  {
    $this->mailSender = new PHPMailer();
    $this->mailSender->isSMTP();
    $this->mailSender->SMTPAuth = true;
    $this->mailSender->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $this->mailSender->Host = "smtp.example.com";
    $this->mailSender->Port = 587;

    $this->mailSender->Username = "you@example.com";
    $this->mailSender->Password = "pssword";
    $this->mailSender->setFrom("you@example.com", "Tiju Info Sport");
  }

  public function send(string $to, string $name, string $subject, string $body, bool $isHTML = false): bool
  {
    $this->mailSender->addAddress($to, $name);
    $this->mailSender->isHTML($isHTML);
    $this->mailSender->Subject = $subject;
    $this->mailSender->Body = $body;

    return $this->mailSender->send();
  }
}
