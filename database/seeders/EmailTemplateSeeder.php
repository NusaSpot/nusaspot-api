<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'constant' => 'welcome',
                'template_for' => 'Welcome',
                'template_header' => 'Complete Your Registration',
                'template_body' => 'Hey {{name}},

Selamat datang!

Silakan gunakan OTP di bawah ini untuk memvalidasi akun Anda.

{{otp}}

OTP ini berlaku selama 5 menit.',
                'button_name' => null,
                'button_link' => null,
            ],[
                'constant' => 'forgot-password',
                'template_for' => 'Forgot Password',
                'template_header' => 'Password Reset',
                'template_body' => 'Hey {{name}},

You have requested to reset your password. Please click on the link below to setup a new password.
The password reset link is valid for 5 minutes.
',
                'button_name' => 'Reset Now',
                'button_link' => 'reset',
            ],
        ];

        EmailTemplate::query()->truncate();

        foreach ($templates as $template) {
          
            if($template['button_link']){
                $template['button_link'] = config('app.url') . $template['button_link'];
            }
           
            EmailTemplate::create($template);
        }
    }
}
