<?php

namespace Modules\FormBuilder\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AutomateUserCreationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $settings = [
            [
                "key" => "automate_user_creation_email",
                "display_name" => "User creation email",
                "value" => (
                    "<h2>Dear {first_name} {last_name},</h2>".
                    "<p>We're writing to let you know that your account is now set up and ready to use. To ensure the security of your account, we recommend that you reset your password.</p>".
                    "{reset_password_button}".
                    "<p>Thank you for joining {app_name}. We look forward to having you as a member!</p>".
                    "<p>&nbsp;</p>".
                    "<p>Regards,<br />{app_name}</p>"
                ),
                "group" => "form_builder.email",
                "order" => "1"
            ],
            [
                "key" => "automate_user_update_email",
                "display_name" => "User update email",
                "value" => (
                    "<h2>Dear {first_name} {last_name},</h2>".
                    "<p>We wanted to let you know that your profile has been updated.</p>".
                    "{login_button}".
                    "<p>Thank you for choosing {app_name}. We look forward to continuing to serve you.</p>".
                    "<p>&nbsp;</p>".
                    "<p>Regards,<br />{app_name}</p>"
                ),
                "group" => "form_builder.email",
                "order" => "2"
            ],

        ];

        foreach ($settings as $setting) {
            Setting::factory()->create(array_merge($setting, [
                "created_at" => now(),
                "updated_at" => now(),
            ]));
        }
    }
}
