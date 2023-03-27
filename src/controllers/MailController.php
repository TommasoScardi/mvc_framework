<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Services\Mailer;

class MailController extends Controller
{
    private Mailer $mailSender;

    public function __construct(Mailer $mailSender)
    {
        $this->mailSender = $mailSender;
    }
    public function Index(Request $req, Response $res)
    {
        $res->end("send mail to someone using /Send/{email} endpoint");
    }

    public function Send(Request $req, Response $res)
    {
        $mailAdd = $req->getID();
        if (!Mailer::validateMail($mailAdd))
        {
            $res->end("email invalid", 400);
            return;
        }
        $mailSentRes = $this->mailSender->addReceivers($mailAdd)
            ->addSubject("test email sender")
            ->html("user_ban.html", ["username" => $mailAdd], "ban")
            ->send();
        if ($mailSentRes->status)
        {
            $res->end("mail sent successful");
        }
        else
        {
            $res->error(400, "error sending the mail: " . $mailSentRes->message);
        }
    }
}
