<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Application;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;
    
  
 
    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
        
    }

    /**
     * Build verification email
     */
    public function build()
    {
        

        return $this->subject('Verify Your Email Address')
            ->markdown('emails.verify-email');
            //->view('emails.verify-email')
           
    }

    
}
