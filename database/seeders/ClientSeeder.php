<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Faker\Factory as Faker;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        for ($i = 0; $i < 50; $i++) {
            // توليد أرقام هواتف تحاكي النمط المحلي (تبدأ بـ 09 أو 01 تليها 8 أرقام)
            $phonePrefix = $faker->randomElement(['09', '01']);

            Client::create([
                'name' => $faker->name,
                'phone' => $phonePrefix . $faker->numerify('########'),
                'image' => null, // نترك الصورة فارغة في البيانات الوهمية لتسريع الاختبار
            ]);
        }
    }
}
