<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['group_name' => 'smtp', 'key_name' => 'enabled', 'label' => 'SMTP Enabled', 'value' => '0', 'type' => 'boolean', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'host', 'label' => 'SMTP Host', 'value' => 'smtp.example.com', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'port', 'label' => 'SMTP Port', 'value' => '587', 'type' => 'integer', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'username', 'label' => 'SMTP Username', 'value' => 'smtp-user-demo', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'password', 'label' => 'SMTP Password', 'value' => 'smtp-password-demo', 'type' => 'password', 'is_encrypted' => true, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'encryption', 'label' => 'SMTP Encryption', 'value' => 'tls', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'from_address', 'label' => 'From Address', 'value' => 'noreply@segreens.test', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'smtp', 'key_name' => 'from_name', 'label' => 'From Name', 'value' => 'SEGreens', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'fcm', 'key_name' => 'enabled', 'label' => 'FCM Enabled', 'value' => '0', 'type' => 'boolean', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'fcm', 'key_name' => 'project_id', 'label' => 'FCM Project ID', 'value' => 'segreens-demo-project', 'type' => 'string', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'fcm', 'key_name' => 'server_key', 'label' => 'FCM Server Key (Legacy)', 'value' => 'demo-fcm-server-key', 'type' => 'password', 'is_encrypted' => true, 'is_active' => true],
            ['group_name' => 'fcm', 'key_name' => 'credentials_json', 'label' => 'FCM Credentials JSON', 'value' => '{"type":"service_account","project_id":"segreens-demo-project","private_key_id":"demo","private_key":"-----BEGIN PRIVATE KEY-----\\nDEMO\\n-----END PRIVATE KEY-----\\n","client_email":"demo@segreens-demo-project.iam.gserviceaccount.com","client_id":"1234567890"}', 'type' => 'json', 'is_encrypted' => true, 'is_active' => true],
            ['group_name' => 'app', 'key_name' => 'require_email_verification', 'label' => 'Require Email Verification', 'value' => '1', 'type' => 'boolean', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'app', 'key_name' => 'enable_order_email_notification', 'label' => 'Enable Order Email Notification', 'value' => '1', 'type' => 'boolean', 'is_encrypted' => false, 'is_active' => true],
            ['group_name' => 'app', 'key_name' => 'enable_order_push_notification', 'label' => 'Enable Order Push Notification', 'value' => '1', 'type' => 'boolean', 'is_encrypted' => false, 'is_active' => true],
        ];

        foreach ($settings as $setting) {
            SystemSetting::query()->updateOrCreate(
                [
                    'group_name' => $setting['group_name'],
                    'key_name' => $setting['key_name'],
                ],
                $setting,
            );
        }
    }
}
