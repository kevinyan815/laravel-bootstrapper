<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = ['email' => 'first@admin.com', 'name' => 'admin'];
        \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('123pass'),
        ]);

        $this->command->info("New Admin created. Username: {$data['email']},  Password: 123pass");
    }
}
