<?php
/**
 * Created by Felipe Quintella.
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */

namespace ccm;

require_once ROOT . "/class/secure.class.php";
require_once ROOT . "/class/singleton.class.php";
require_once ROOT . "/phpmailer/class.phpmailer.php";

//require_once ROOT."/data/templates/mailTemplate.list.php";

class mailManager extends singleton
{

    protected $tList;

    public function getList()
    {
        if ($this->tList == null) $this->tList = getTemplateList();
        return $this->tList;
    }

    public function sendMail($to, $template, $params)
    {

        $mail = new PHPMailer();

        $mail->IsSMTP();                                      // Set mailer to use SMTP
        $mail->CharSet = 'UTF-8';
        if (gethostname() == PRODServer)
            $mail->Host = SMTPServer;                            // Specify main and backup server
        else $mail->Host = DevSMTPServer;
        $mail->SMTPAuth = false;                               // Enable SMTP authentication
        //$mail->Username = 'jswan';                            // SMTP username
        //$mail->Password = 'secret';                           // SMTP password
        //$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

        $mail->From = FROM;
        $mail->FromName = FROM_NAME;

        $mail->AddAddress($to);


        //$mail->AddReplyTo('info@example.com', 'Information');
        //$mail->AddCC('cc@example.com');
        //$mail->AddBCC('bcc@example.com');

        //$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        //$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        $mail->IsHTML(true);                                  // Set email format to HTML


        $temp = $this->find($template);

        //$head = str_replace("%from%", $from, $head);
        //$head = str_replace("%to%", $to, $head);

        $body = $temp->getBody();
        foreach ($params as $key => $value) {

            $body = str_replace("%" . $key . "%", $value, $body);

        }


        $mail->Subject = $temp->getSubject();
        $mail->Body = $body;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        //ini_set ("display_errors", "1");

        //error_reporting(E_ALL);

        $imgs = $temp->getImgs();
        $id = 10001;
        foreach ($imgs as $key => $value) {
            $mail->AddEmbeddedImage($value, $key);
            $id++;
        }


        $mail->Send();

        /*if(!$mail->Send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            exit;
        }

        mail($to,$temp->getSubject(),$body, $head);

        */

    }

    public function find($name)
    {
        if ($this->tList == null) $this->tList = getTemplateList();

        for ($i = 1; $i <= $this->tList->totalNodes(); $i++) {
            $val = $this->tList->readNode($i);
            if ($val->getName() == $name) return $val;
        }

    }


}