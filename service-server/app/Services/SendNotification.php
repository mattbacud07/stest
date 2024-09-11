<?php

namespace App\Services;

use App\Mail\EHNotification;
use Illuminate\Support\Facades\Mail;

class SendNotification{
    public function send_email($emailData){
        ignore_user_abort(true);
        ob_start();
        ob_flush();
        flush();

        if(function_exists('fastcgi_finish_request')){
            fastcgi_finish_request();
        }

        Mail::to($emailData['to'])->send(new EHNotification($emailData['name']));
    }
}