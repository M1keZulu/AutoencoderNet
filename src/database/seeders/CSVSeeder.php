<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CSVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = array_map('str_getcsv', file(database_path('seeders/images.csv')));
        $users = array_map('str_getcsv', file(database_path('seeders/users.csv')));
        $comments = array_map('str_getcsv', file(database_path('seeders/comments.csv')));
    
        foreach ($images as $image) {
            DB::table('images')->insert([
                'name' => $image[0],
                'description' => $image[1],
                'price' => $image[2],
                'currency' => $image[3],
                'image_path' => $image[4]
            ]);
        }
    
        foreach ($users as $user) {
            DB::table('users')->insert([
                'name' => $user[0],
                'email' => $user[1],
                'password' => $user[2],
                'role' => $user[3],
            ]);
        }
    
        foreach ($comments as $comment) {
            DB::table('comments')->insert([
                'content' => $comment[0],
                'user_id' => $comment[1],
                'image_id' => $comment[2],
                'created_at' => $comment[3],
                'updated_at' => $comment[4],
            ]);
        }
    
    }
}
