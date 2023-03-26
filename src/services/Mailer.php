<?php

namespace MvcFramework\Services;

use MvcFramework\Core\Application;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use MvcFramework\Core\Service;

class Mailer implements Service
{
    private string $senderEmail;
    private string $host;
    private string $username;
    private string $pwd;
    private int $port;

    private PHPMailer $mail;

    public function __construct(string $senderEmail, string $host, string $username, string $pwd, int $port = 465)
    {
        $this->senderEmail = $senderEmail;
        $this->host = $host;
        $this->username = $username;
        $this->pwd = $pwd;
        $this->port = $port;
    }

    public function init()
    {
        $this->senderEmail = $this->senderEmail;
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF; //SMTP::DEBUG_SERVER

        //Mail Server - User Data
        $this->mail->Host = $this->host;
        $this->mail->Username = $this->username;
        $this->mail->Password = $this->pwd;

        //Mail Server - Port
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $this->port;

        //Mail Server - Encryption
        $this->mail->SMTPAuth   = true;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        return true;
    }

    private function emptyFields()
    {
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
        $this->mail->clearBCCs();
        $this->mail->clearCCs();
        $this->mail->clearReplyTos();
    }

    /**
     * adds the receivers
     * @param string|array $rec an array key => (user email) value => (user name)
     * @param string $name used only if `$rec` is a string
     * @return $this 
     */
    public function addReceivers(string|array $rec, string $name = "")
    {
        if (is_array($rec))
        {
            foreach ($rec as $k => $v)
            {
                $this->mail->addAddress($k, $v);
            }
        }
        else
        {
            $this->mail->addAddress($rec, $name);
        }
        return $this;
    }

    /**
     * adds the copy carbon copy (copia conoscenza)
     * @param array $rec an array key => (user email) value => (user name)
     * @return $this 
     */
    public function addCC(array $cc)
    {
        foreach ($cc as $k => $v)
        {
            $this->mail->addCC($k, $v);
        }
        return $this;
    }

    /**
     * adds the copy blinded carbon copy (copia conoscenza nascosta)
     * @param array $rec an array key => (user email) value => (user name)
     * @return $this 
     */
    public function addBCC(array $bcc)
    {
        foreach ($bcc as $k => $v)
        {
            $this->mail->addBCC($k, $v);
        }
        return $this;
    }

    public function addSubject(string $sub)
    {
        $this->mail->Subject = $sub;
        return $this;
    }

    /**
     * adds a attachment from filesystem
     * @param string $path the path file including the file name
     * @param string $newName the new name to display on the mail
     * @return $this 
     * @throws Exception 
     */
    public function addAttach(string $path, string $newName)
    {
        $this->mail->addAttachment($path, $newName);
        return $this;
    }

    public function text(string $text)
    {
        $this->mail->isHtml(false);
        $this->mail->Body = $text;
        return $this;
    }

    /**
     * load into body an html file from a template page
     * @param string $templateName the template name to search into template folder 
     * @param array $data an array key value of values to replace into template
     * @param string $altText the text for client who does not support html mails
     * @return $this
     */
    public function html(string $templateName, array $data, string $altText = "")
    {
        $this->mail->isHTML(true);
        $body = file_get_contents(Application::$ROOT_PATH."templates/email/".$templateName);
        $placeholders = array_map(fn($key) => "{{$key}}", array_keys($data));
        $this->mail->Body = str_replace($placeholders, $data, $body);
        $this->mail->AltBody = $altText;
        return $this;
    }

    public function send()
    {
        $ret = $this->mail->send();
        $this->emptyFields();
        return $ret === true ? $ret : $this->mail->ErrorInfo;
    }

    public static function validateMail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
