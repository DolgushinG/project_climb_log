<?php

namespace Database\Seeders;

use Database\Factories\EventFactory;
use DB;
use Illuminate\Database\Seeder;

class AdminRoleAndUsersSeeder extends Seeder
{

    const COUNT_EVENTS = 2;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_users = [
            array('id' => '1','username' => 'Dolgushin','password' => '$2y$10$J5FukTE5t0JLA1YyHxSQeeopkC3QmJuJqaQiZUqFmhYZYmGMN0Dfy','name' => 'admin','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' =>'dolgushin','climbing_gym_link' => 'https://admin.ru','address' => 'ул. Хомяковская, д. 6','city' => 'Москва','is_access_to_create_event' => 1,'phone' => '+7 (4872) 71-71-25','optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:56:42','updated_at' => '2024-01-18 07:56:42'),
            array('id' => '2','username' => 'Tester2','password' => '$2y$10$V3NeMDqDQ7DG8GE8jIphdOciL4.CT.7v5rUVC20tCaUd7w6/aVC4C','name' => 'admin','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'admin','climbing_gym_link' => 'https://admin.ru','address' => 'ул. Хомяковская, д. 6','city' => 'Москва','is_access_to_create_event' => 1,'phone' => '+7 (4872) 71-71-25','optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:56:42','updated_at' => '2024-01-18 07:56:42'),
            array('id' => '3','username' => 'voshod','password' => '$2y$10$fAYZjV4GD6DMSTyz2PzsZeyOzDJ5.z5goat1sTZEQSHu1.YnTeRHW','name' => 'voshod','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'voshod','climbing_gym_link' => 'https://voshod-ts.ru','address' => 'ул. Хомяковская, д. 6','city' => 'Тула','is_access_to_create_event' => 1,'phone' => '+7 (4872) 71-71-25','optional_phone' => NULL,'avatar' => 'images/profile_images/cdaeb13fdfbeff85f7091876ea513230.jpg','remember_token' => '36H0UUdhT1nzhCVmrUqb2AvpCqQqX2G6PPi0g22TYS7QSqDUnYFJErxu318W','created_at' => '2024-03-04 07:27:48','updated_at' => '2024-03-04 07:27:48'),
            array('id' => '4','username' => 'elcapitan','password' => '$2y$10$/Md..dQ6CBDXwrdx5Fx06.gewvtRe.niXbiu3cOxLTfag5qbuqZoW','name' => 'elcapitan','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'elcapitan','climbing_gym_link' => 'https://elcapitan.club/','address' => 'ул. Арсенальная 6','city' => 'Санкт-Петербург','is_access_to_create_event' => 1,'phone' => '+7 (999) 537-54-51','optional_phone' => NULL,'avatar' => 'images/IMG_0100.png','remember_token' => 'VGWkPctOEYwUen5ylI7oHDXzN0Zkv8rBg0WdydNJE8smnDdHKKmlmfFVOB02','created_at' => '2024-03-04 07:27:48','updated_at' => '2024-03-04 07:27:48'),
            array('id' => '5','username' => 'tengus','password' => '$2y$10$JS2jOZZjOlMJ/Qnfrz4r4OYsf9q8arguiLem6lvHvGzjaCzzvd97a','name' => 'tengus','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => 'tengus','climbing_gym_link' => 'https://tengus.ru','address' => 'м. Мичуринский пр-т, ул. Лобачевского, 114','city' => 'Москва','is_access_to_create_event' => 1,'phone' => '+7 958 295-19-15','optional_phone' => NULL,'avatar' => 'images/CleanShot 2024-03-25 at 14.11.29@2x.png','remember_token' => 'c8P5VhyEYSiEvF6ySNfJJmDzwRcMomKSsMrrDLUISVPpxs24TZF72MpkRH4R','created_at' => '2024-03-04 07:27:48','updated_at' => '2024-03-04 07:27:48'),
            ];
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
            array('user_id' => '2','permission_id' => '16','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '17','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '2','permission_id' => '18','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '15','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '16','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '17','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '18','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '15','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '16','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '17','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '4','permission_id' => '18','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '15','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '16','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '17','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '5','permission_id' => '18','created_at' => NULL,'updated_at' => NULL)
        );
        $admin_role_users = array(
            array('role_id' => '1','user_id' => '1','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('role_id' => '2','user_id' => '5','created_at' => NULL,'updated_at' => NULL),
        );
        \Illuminate\Support\Facades\DB::table('admin_users')->insert($admin_users);
        \Illuminate\Support\Facades\DB::table('admin_role_users')->insert($admin_role_users);
        \Illuminate\Support\Facades\DB::table('admin_user_permissions')->insert($admin_user_permissions);

    }
}
