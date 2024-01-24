<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AdminRoleAndUsersSeeder extends Seeder
{

    const COUNT_EVENTS = 16;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_users = [array('id' => '1','username' => 'admin','password' => '$2y$10$eWsiQ1nirz.qRxKAVTie3O7Y8qzIFG493dhnB5KbkQSJNMRAQPEeq','name' => 'admin','contact_email' => NULL,'contact_link' => NULL,'climbing_gym_name' => NULL,'climbing_gym_link' => NULL,'address' => NULL,'city' => NULL,'phone' => NULL,'optional_phone' => NULL,'avatar' => NULL,'remember_token' => NULL,'created_at' => '2024-01-18 07:56:42','updated_at' => '2024-01-18 07:56:42'),];
        for($i = 2; $i <= self::COUNT_EVENTS; $i++){
            $admin_users[] = array('id' => $i,
                'username' => 'admin'.$i,
                'password' => '$2y$10$eWsiQ1nirz.qRxKAVTie3O7Y8qzIFG493dhnB5KbkQSJNMRAQPEeq',
                'name' => 'admin'.$i,
                'contact_email' => NULL,
                'contact_link' => NULL,
                'climbing_gym_name' => NULL,
                'climbing_gym_link' => NULL,
                'address' => NULL,
                'city' => NULL,
                'phone' => NULL,
                'optional_phone' => NULL,
                'avatar' => NULL,
                'remember_token' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL);
        }
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
            array('user_id' => '3','permission_id' => '2','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '3','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '4','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '7','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '8','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '9','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '10','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '13','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '15','created_at' => NULL,'updated_at' => NULL),
            array('user_id' => '3','permission_id' => '16','created_at' => NULL,'updated_at' => NULL)
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
