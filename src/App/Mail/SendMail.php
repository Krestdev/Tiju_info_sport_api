<?php

declare(strict_types=1);

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SendMail
{
  private PHPMailer $mailSender;
  public function __construct()
  {
    $this->mailSender = new PHPMailer();
    $this->mailSender->SMTPDebug = SMTP::DEBUG_SERVER;
    $this->mailSender->isSMTP();
    $this->mailSender->SMTPAuth = true;
    $this->mailSender->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $this->mailSender->Host = $_ENV["SMTP_SERVER"];
    $this->mailSender->Port = $_ENV["SMTP_PORT"];

    $this->mailSender->Username = $_ENV["SMTP_EMAIL"];
    $this->mailSender->Password = $_ENV["SMTP_PASSWORD"];
    $this->mailSender->setFrom($_ENV["SMTP_EMAIL"], "Tiju Info Sport");
  }

  public function send(string $to, string $name, string $subject, string $body, bool $isHTML = false)
  {
    $this->mailSender->addAddress($to, $name);
    $this->mailSender->isHTML($isHTML);
    $this->mailSender->Subject = $subject;
    $this->mailSender->Body = $body;
    $this->mailSender->send();
    return;
  }
}
