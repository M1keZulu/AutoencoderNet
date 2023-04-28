<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $faker = Faker::create();

        foreach(range(1, 8) as $index) {
            $image = Image::make($faker->imageUrl(640, 480));
            $unique = uniqid();
            $filename = storage_path('app/public/images/' . $unique . '.jpg');
            $image->save($filename);

            DB::table('images')->insert([
                'name' => $faker->name,
                'description' => $faker->sentence,
                'price' => $faker->unique()->numberBetween(100000, 999999),
                'currency' => $faker->randomElement(['usd', 'gbp']),
                'image_path' => $unique . '.jpg',
            ]);
        }

        foreach(range(1, 8) as $index) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password'),
                'role' => $faker->randomElement(['admin', 'user']),
            ]);
        }

        foreach(range(1, 8) as $index) {
            $image_ids = DB::table('images')->pluck('id')->toArray();
            $user_ids = DB::table('users')->pluck('id')->toArray();


            DB::table('comments')->insert([
                'content' => $faker->sentence,
                'user_id' => $faker->randomElement($user_ids),
                'image_id' => $faker->randomElement($image_ids),
                'created_at' => $faker->dateTimeBetween('-1 years', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 years', 'now'),
            ]);
        }
    }
}
