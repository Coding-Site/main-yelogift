<?php

namespace App\Traits;

trait SendMailTrait
{
    public function send_mail($to_email=null, $name=null, $subject_email=null, $content=null)
    {
        $to = $to_email;
        $subject = $subject_email;
        $message = "Dear ".$name."<br>";
        $message .= $content;

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <info@yelogift.net>' . "\r\n";
        mail($to,$subject,$message,$headers);
    }
}
