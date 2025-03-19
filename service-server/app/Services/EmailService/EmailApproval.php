<?php

namespace App\Services\EmailService;

use App\Mail\EHNotification;
use Exception;
use Illuminate\Support\Facades\Mail;

class EmailApproval {
    public function approval_send_email($usersArray,  $link, $subject){
        try{
            Mail::bcc($usersArray)->send(new EHNotification($link, $subject));
        }catch(Exception $e){
            return response()->json([
                'error' => 'Error sending emails'
            ]);
        }
    }


}