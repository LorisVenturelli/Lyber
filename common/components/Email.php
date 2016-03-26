<?php

namespace Lyber\Common\Components;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Email
{
    private static $transport;

    private static function init(){
        self::$transport = Swift_SmtpTransport::newInstance(Config::get('smtp_mail', 'host'), Config::get('smtp_mail', 'port'));
        self::$transport->setUsername(Config::get('smtp_mail', 'user'));
        self::$transport->setPassword(Config::get('smtp_mail', 'password'));
    }


    /**
     * Service method to fastly send a message
     * @param $subject
     * @param $body
     */
    public static function send($to, $subject, $body)
    {
        if(!is_array($to))
            $to = array($to);

        Core::require_data([
            $subject => ['string'], 
            $body => ['notempty','string']
        ]);
        
        if(empty(self::$transport))
            self::init();

        $mailer = Swift_Mailer::newInstance(self::$transport);

        $message = Swift_Message::newInstance()
                ->setFrom(Config::get('smtp_mail', 'from'), Config::get('smtp_mail', 'name'))
                ->setTo($to)
                ->setSubject($subject)
                ->setBody($body, 'text/html');

        return $mailer->send($message);
    }
}