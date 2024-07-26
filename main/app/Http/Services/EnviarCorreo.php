<?php
namespace App\Http\Services;

require_once('phpMailer/PHPMailerAutoload.php');
require_once('phpMailer/PHPMailer.php');
require_once('phpMailer/SMTP.php');
require_once('phpMailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;

class EnviarCorreo
{
    private $host, $host2, $host3, $smtpAuth, $userName, $userName2, $password, $password2, $smtpSecure, $port, $from, $fromName, $from2, $fromName2;

    function __construct()
    {
        $this->host = 'sigespro.com.co';
        $this->host3 = 'mail.sigespro.com.co';
        $this->smtpAuth = true;
        // $this->userName='_mainaccount@sigespro.com.co';
        $this->userName = 'info@sigespro.com.co';
        $this->password = 'Proh2019*';
        $this->smtpSecure = 'ssl';
        $this->port = 465;
        $this->from = 'info@sigespro.com.co';
        $this->fromName = 'Sigespro';

        $this->host2 = 'smtp.gmail.com';
        $this->userName2 = 'facturacionelectronicacont@prohsa.com';
        $this->password2 = '2022Proh';
        $this->from2 = 'facturacionelectronicacont@prohsa.com';
        $this->fromName2 = 'ProH S.A. (Productos Hospitalarios S.A.)';
    }

    function __destruct()
    {
    }


    public function EnviarMail($des, $subject, $msg, $file)
    {

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";
        $mail->SMTPAuth   = true;
        $mail->Host = $this->host;
        $mail->Helo = $this->host; //Muy importante para que llegue a hotmail y otros
        $mail->SMTPAuth = $this->smtpAuth;
        $mail->Username = $this->userName;
        $mail->Password = $this->password;
        $mail->SMTPSecure = $this->smtpSecure;
        $mail->Port = $this->port;
        $mail->setFrom($this->from);
        $mail->FromName   =  $this->fromName;
        $mail->Subject    = $subject;
        $mail->CharSet = 'UTF-8';
        if ($des != '') {
            $mail->AddAddress($des);
        }

        $mail->AddAddress('talentohumano@prohsa.com');

        $mail->IsHTML(true);
        //$mail->MsgHTML($msg);
        $mail->Body = $msg;
        if ($file != '') {
            $mail->AddAttachment($file);
        }

        $mail->Send();
    }

    public function EnviarMailProductos($des, $subject, $msg, $file)
    {

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";
        $mail->SMTPAuth   = true;
        $mail->SMTPKeepAlive = true;
        $mail->Host = $this->host3;
        $mail->Helo = $this->host; //Muy importante para que llegue a hotmail y otros
        $mail->SMTPAuth = $this->smtpAuth;
        $mail->Username = $this->userName;
        $mail->Password = $this->password;
        $mail->SMTPSecure = $this->smtpSecure;
        $mail->Port = $this->port;
        $mail->setFrom($this->from);
        $mail->FromName   =  $this->fromName;
        $mail->Subject    = $subject;
        $mail->CharSet = 'UTF-8';
        if (count($des) > 0) {
            foreach ($des as  $d) {
                $mail->AddAddress($d);
            }
        }
        $mail->AddAddress('augustoacarrillo@hotmail.com');

        $mail->IsHTML(true);
        $mail->Body = $msg;
        $mail->Send();
    }

    public function EnviarFacturaDian($des, $subject, $msg, $xml, $fact)
    {

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";
        $mail->SMTPAuth   = true;
        $mail->Host = $this->host2;
        $mail->Helo = $this->host2; //Muy importante para que llegue a hotmail y otros
        //$mail->Ehlo =$this->host2; //Muy importante para que llegue a hotmail y otros
        $mail->SMTPAuth = $this->smtpAuth;
        $mail->Username = $this->userName2;
        $mail->Password = $this->password2;
        $mail->SMTPSecure = $this->smtpSecure;
        $mail->Port = $this->port;
        $mail->setFrom($this->from2);
        $mail->FromName   =  $this->fromName2;
        $mail->Subject    = $subject;
        $mail->CharSet = 'UTF-8';
        //$mail->SMTPDebug =4 ; //para ver los errores del lado del cliente y servidor
        //$mail->Debugoutput = 'html';
        if ($des != '') {
            $mail->AddAddress($des);
        }
        $mail->AddAddress("facturacionelectronicacont@prohsa.com");
        $mail->IsHTML(true);
        $mail->Body = $msg;
        if ($xml != '') {
            $mail->AddAttachment($xml);
        }
        if ($fact != '') {
            $mail->AddAttachment($fact);
        }

        $env = $mail->Send();

        if ($env) {
            $resp["Estado"] = "Exito";
            $resp["Respuesta"] = "El Correo se ha enviado Correctamente";
        } else {
            $resp["Estado"] = "Error";
            $resp["Respuesta"] = "Error al Enviar Correo al Cliente: " . $mail->ErrorInfo;
        }

        return ($resp);
    }
}
