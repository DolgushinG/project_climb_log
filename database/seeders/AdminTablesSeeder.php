<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // base tables
        \Encore\Admin\Auth\Database\Menu::truncate();
        \Encore\Admin\Auth\Database\Menu::insert(
            [
                [
                    "parent_id" => 0,
                    "order" => 1,
                    "title" => "Главная",
                    "icon" => "fa-bar-chart",
                    "uri" => "/",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 2,
                    "title" => "Admin",
                    "icon" => "fa-tasks",
                    "uri" => "",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 3,
                    "title" => "Users",
                    "icon" => "fa-users",
                    "uri" => "auth/users",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 4,
                    "title" => "Roles",
                    "icon" => "fa-user",
                    "uri" => "auth/roles",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 5,
                    "title" => "Permission",
                    "icon" => "fa-ban",
                    "uri" => "auth/permissions",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 6,
                    "title" => "Menu",
                    "icon" => "fa-bars",
                    "uri" => "auth/menu",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 7,
                    "title" => "Operation log",
                    "icon" => "fa-history",
                    "uri" => "auth/logs",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Соревнования",
                    "icon" => "fa-feed",
                    "uri" => "events",
                    "permission" => "owner.events"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Квалификация",
                    "icon" => "fa-coffee",
                    "uri" => "participants",
                    "permission" => "owner.participants"
                ],
                [
                    "parent_id" => 0,
                    "order" => 7,
                    "title" => "Helpers",
                    "icon" => "fa-gears",
                    "uri" => NULL,
                    "permission" => "ext.helpers"
                ],
                [
                    "parent_id" => 10,
                    "order" => 8,
                    "title" => "Scaffold",
                    "icon" => "fa-keyboard-o",
                    "uri" => "helpers/scaffold",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 10,
                    "order" => 9,
                    "title" => "Database terminal",
                    "icon" => "fa-database",
                    "uri" => "helpers/terminal/database",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 10,
                    "order" => 10,
                    "title" => "Laravel artisan",
                    "icon" => "fa-terminal",
                    "uri" => "helpers/terminal/artisan",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 10,
                    "order" => 11,
                    "title" => "Routes",
                    "icon" => "fa-list-alt",
                    "uri" => "helpers/routes",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Полуфинал",
                    "icon" => "fa-adn",
                    "uri" => "/semifinal-stage",
                    "permission" => "owner.result.route.semifinal.stage"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Категории участников",
                    "icon" => "fa-angellist",
                    "uri" => "participants-categories",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Генератор трасс",
                    "icon" => "fa-codepen",
                    "uri" => "grades",
                    "permission" => "owner.grades"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Форматы",
                    "icon" => "fa-align-center",
                    "uri" => "formats",
                    "permission" => "formats"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Сеты",
                    "icon" => "fa-adjust",
                    "uri" => "sets",
                    "permission" => "owner.sets"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Коэффициент трасс",
                    "icon" => "fa-anchor",
                    "uri" => "event-and-coefficient-route",
                    "permission" => "owner.event.and.coefficient.route"
                ],
                [
                    "parent_id" => 0,
                    "order" => 0,
                    "title" => "Финал",
                    "icon" => "fa-cogs",
                    "uri" => "final-stage",
                    "permission" => "owner.result.route.final.stage"
                ]
            ]
        );

        \Encore\Admin\Auth\Database\Permission::truncate();
        \Encore\Admin\Auth\Database\Permission::insert(
            [
                [
                    "name" => "All permission",
                    "slug" => "*",
                    "http_method" => "",
                    "http_path" => "*"
                ],
                [
                    "name" => "Dashboard",
                    "slug" => "dashboard",
                    "http_method" => "GET",
                    "http_path" => "/"
                ],
                [
                    "name" => "Login",
                    "slug" => "auth.login",
                    "http_method" => "",
                    "http_path" => "/auth/login\r\n/auth/logout"
                ],
                [
                    "name" => "User setting",
                    "slug" => "auth.setting",
                    "http_method" => "GET,PUT",
                    "http_path" => "/auth/setting"
                ],
                [
                    "name" => "Auth management",
                    "slug" => "auth.management",
                    "http_method" => "",
                    "http_path" => "/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs"
                ],
                [
                    "name" => "Admin helpers",
                    "slug" => "ext.helpers",
                    "http_method" => "",
                    "http_path" => "/helpers/*"
                ],
                [
                    "name" => "Events",
                    "slug" => "owner.events",
                    "http_method" => "",
                    "http_path" => "/events*"
                ],
                [
                    "name" => "Participants",
                    "slug" => "owner.participants",
                    "http_method" => "GET,POST,PUT,PATCH,OPTIONS",
                    "http_path" => "/participants"
                ],
                [
                    "name" => "Participants categories",
                    "slug" => "owner.participants.categories",
                    "http_method" => "GET,POST",
                    "http_path" => "/participants-categories"
                ],
                [
                    "name" => "Result Route SemiFinal Stage",
                    "slug" => "owner.result.route.semifinal.stage",
                    "http_method" => "",
                    "http_path" => "/semifinal-stage*"
                ],
                [
                    "name" => "Grades",
                    "slug" => "owner.grades",
                    "http_method" => "",
                    "http_path" => "/grades*"
                ],
                [
                    "name" => "Formats",
                    "slug" => "formats",
                    "http_method" => "",
                    "http_path" => "/formats*"
                ],
                [
                    "name" => "Sets",
                    "slug" => "owner.sets",
                    "http_method" => "",
                    "http_path" => "/sets*"
                ],
                [
                    "name" => "Event And Coefficient Route",
                    "slug" => "owner.event.and.coefficient.route",
                    "http_method" => "GET",
                    "http_path" => "/event-and-coefficient-route"
                ],
                [
                    "name" => "Export",
                    "slug" => "export",
                    "http_method" => "",
                    "http_path" => "/exports*"
                ],
                [
                    "name" => "Result Route Final Stage",
                    "slug" => "owner.result.route.final.stage",
                    "http_method" => "",
                    "http_path" => "/final-stage*"
                ]
            ]
        );

        \Encore\Admin\Auth\Database\Role::truncate();
        \Encore\Admin\Auth\Database\Role::insert(
            [
                [
                    "name" => "Administrator",
                    "slug" => "administrator"
                ],
                [
                    "name" => "Owner",
                    "slug" => "owner.climging.gym"
                ]
            ]
        );

        // pivot tables
        DB::table('admin_role_menu')->truncate();
        DB::table('admin_role_menu')->insert(
            [
                [
                    "role_id" => 1,
                    "menu_id" => 2
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 8
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 9
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 10
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 15
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 17
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 18
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 19
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 20
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 21
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 22
                ],
                [
                    "role_id" => 2,
                    "menu_id" => 8
                ],
                [
                    "role_id" => 2,
                    "menu_id" => 9
                ],
                [
                    "role_id" => 2,
                    "menu_id" => 15
                ],
                [
                    "role_id" => 2,
                    "menu_id" => 21
                ]
            ]
        );

        DB::table('admin_role_permissions')->truncate();
        DB::table('admin_role_permissions')->insert(
            [
                [
                    "role_id" => 1,
                    "permission_id" => 1
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 2
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 3
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 4
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 7
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 8
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 9
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 10
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 13
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 15
                ],
                [
                    "role_id" => 2,
                    "permission_id" => 16
                ]
            ]
        );

        // finish
    }
}
