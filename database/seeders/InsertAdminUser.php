<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsertAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    $user = new \App\Models\User;
    $user->name = 'Admin'; // Explicitly assigned attributes bypass $fillable restrictions
    $user->email = 'admin@tabulation.test';
    $user->password = bcrypt('secret123');
    $user->save();
}


}
