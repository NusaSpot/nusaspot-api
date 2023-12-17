<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestOTP extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->subject = 'NutriCare - {{header}}';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $m = new \Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $emailTemplate = EmailTemplate::where('constant', 'welcome')->first();
        $header = $emailTemplate->template_header;
        $subject = $m->render($this->subject, ['header' => $header]);
        $body = $m->render($emailTemplate->template_body, ['name' => $this->user->name, 'otp' => $this->user->otp]);
        $body = str_replace("\r", "", $body);
        $body = array_filter(explode("\n", $body));
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))->subject($subject)
            ->view('mails.mail', ['header' => $header, 'subject' => $subject, 'body' => $body]);
    }

}
