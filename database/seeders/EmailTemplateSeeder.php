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
                'template_header' => 'Selesaikan Registrasi Anda',
                'template_body' => 'Hai {{name}},

Selamat datang di NutriCare!

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
            ],[
                'constant' => 'approved-nutritionist',
                'template_for' => 'Approved Nutritionist',
                'template_header' => 'Pendaftaran Anda Diterima',
                'template_body' => 'Hey {{name}},

Selamat anda dapat bergabung bersama NutriCare, silahkan login ke website NutriCare untuk melanjutkan aktivitas anda.
',
                'button_name' => null,
                'button_link' => null,
            ],[
                'constant' => 'rejected-nutritionist',
                'template_for' => 'rejected Nutritionist',
                'template_header' => 'Pendaftaran Anda Ditolak',
                'template_body' => 'Hai {{name}},

Mohon maaf pendaftaran ditolak, silahkan login ke website NutriCare untuk melihat keterangan lebih lanjut.
',
                'button_name' => null,
                'button_link' => null,
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
