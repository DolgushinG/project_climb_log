<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AdminRoleAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin_users = array(
            array('id' => '1','username' => 'admin','password' => '$2y$10$eWsiQ1nirz.qRxKAVTie3O7Y8qzIFG493dhnB5KbkQSJNMRAQPEeq','name' => 'admin','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => NULL,'climbing_gym_link' => NULL,'address' => NULL,'city' => NULL,'phone' => NULL,'optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:56:42','updated_at' => '2024-01-18 07:56:42'),
            array('id' => '2','username' => 'lu4','password' => '$2y$10$qxlw7f86XcdvjKK9Q6o0ZO5/7lX4ZZ/mhNCy5svnJ19P6OHDqOVxO','name' => 'lu4','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'Скалодром Луч','climbing_gym_link' => 'https://lu4.su','address' => 'ул. спортивная 7','city' => 'Санкт-Петербург','phone' => '79999999999','optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:57:03','updated_at' => '2024-01-18 08:00:06'),
            array('id' => '3','username' => 'voshod','password' => '$2y$10$qxlw7f86XcdvjKK9Q6o0ZO5/7lX4ZZ/mhNCy5svnJ19P6OHDqOVxO','name' => 'voshod','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'Скалодром Восход','climbing_gym_link' => 'https://voshod.ru','address' => 'ул. тульская 7','city' => 'Тула','phone' => '79999999999','optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:57:03','updated_at' => '2024-01-18 08:00:06')
        );
        $admin_user_permissions = array(
            array('user_id' => '2','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '15','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '15','created_at' => NULL,'updated_at' => NULL)
        );
        $admin_role_users = array(
            array('role_id' => '1','user_id' => '1','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '1','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '3','created_at' => NULL,'updated_at' => NULL)
        );
        \Illuminate\Support\Facades\DB::table('admin_users')->insert($admin_users);
        \Illuminate\Support\Facades\DB::table('admin_role_users')->insert($admin_role_users);
        \Illuminate\Support\Facades\DB::table('admin_user_permissions')->insert($admin_user_permissions);

    }
}
