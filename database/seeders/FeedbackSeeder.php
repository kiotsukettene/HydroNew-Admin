<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use App\Models\Device;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $devices = Device::where('is_archived', false)->get();

        if ($users->isEmpty() || $devices->isEmpty()) {
            $this->command->warn('FeedbackSeeder: No users or devices found. Skipping feedback seed.');
            return;
        }

        $categories = [
            'bug_report',
            'feature_request',
            'general_feedback',
            'device_issue',
            'other',
        ];

        $feedbackItems = [
            [
                'category' => 'bug_report',
                'subject' => 'App crashes when opening sensor readings',
                'message' => 'Whenever I tap on the sensor readings tab, the app closes immediately. This started after the last update. I\'m using Android 14.',
            ],
            [
                'category' => 'feature_request',
                'subject' => 'Dark mode support',
                'message' => 'It would be great to have a dark mode option for the app. I use the system at night and the bright screen is hard on the eyes.',
            ],
            [
                'category' => 'general_feedback',
                'subject' => 'Great product!',
                'message' => 'Really happy with the hydroponics system. The app is easy to use and my plants are thriving. Thank you!',
            ],
            [
                'category' => 'device_issue',
                'subject' => 'pH sensor reading incorrect values',
                'message' => 'My pH sensor has been showing values that don\'t match my manual test kit. Could there be a calibration issue?',
            ],
            [
                'category' => 'other',
                'subject' => 'Question about water change schedule',
                'message' => 'How often should I change the water in my setup? The manual says weekly but I have a larger tank.',
            ],
            [
                'category' => 'bug_report',
                'subject' => 'Notifications not appearing',
                'message' => 'I\'m not receiving any push notifications for low water or pH alerts. I\'ve checked app permissions and they seem correct.',
            ],
            [
                'category' => 'feature_request',
                'subject' => 'Export data to CSV',
                'message' => 'Would love to be able to export my sensor history and harvest data to CSV for my own records and analysis.',
            ],
            [
                'category' => 'general_feedback',
                'subject' => null,
                'message' => 'The support team was very helpful when I had setup questions. Quick response and clear instructions.',
            ],
            [
                'category' => 'device_issue',
                'subject' => 'Device keeps disconnecting from WiFi',
                'message' => 'My HydroStation drops connection every few hours. I have to reconnect manually. Router is in the same room.',
            ],
            [
                'category' => 'feature_request',
                'subject' => 'Multiple device dashboard',
                'message' => 'I have 3 setups now. It would be helpful to see all devices on one dashboard view instead of switching between them.',
            ],
        ];

        foreach ($feedbackItems as $index => $item) {
            $user = $users->get($index % $users->count());
            $device = $devices->get($index % $devices->count());

            Feedback::create([
                'user_id' => $user->id,
                'device_id' => $device->id,
                'category' => $item['category'],
                'subject' => $item['subject'] ?? null,
                'message' => $item['message'],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
