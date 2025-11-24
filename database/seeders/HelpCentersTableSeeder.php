<?php

namespace Database\Seeders;

use App\Models\HelpCenter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HelpCentersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $helpArticles = [
            [
                'question' => 'How do I set up my first hydroponic system?',
                'answer' => 'To set up your first hydroponic system, start by choosing a suitable location with adequate lighting. Set up your water tank, add nutrients, and configure your sensors. Connect your device and follow the guided setup process.',
            ],
            [
                'question' => 'What is the ideal pH level for hydroponics?',
                'answer' => 'Most hydroponic plants thrive at a pH level between 6.0 and 6.8. Tomatoes and peppers prefer slightly higher pH around 6.5-6.8, while leafy greens do well at 5.5-6.5.',
            ],
            [
                'question' => 'How often should I monitor sensor readings?',
                'answer' => 'It is recommended to check sensor readings at least daily. Our system provides real-time monitoring, and you can set up alerts for critical parameters.',
            ],
            [
                'question' => 'What does TDS mean and why is it important?',
                'answer' => 'TDS stands for Total Dissolved Solids. It measures the concentration of nutrients in your water. Maintaining proper TDS levels (typically 1000-1500 ppm) is crucial for plant health.',
            ],
            [
                'question' => 'How can I troubleshoot connection issues?',
                'answer' => 'Check your device is powered on and connected to WiFi. Restart the device and ensure it is within range of your WiFi router. If problems persist, check our troubleshooting guide or contact support.',
            ],
        ];

        foreach ($helpArticles as $article) {
            HelpCenter::create($article);
        }
    }
}
