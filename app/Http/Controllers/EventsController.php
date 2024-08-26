<?php

namespace App\Http\Controllers;

use App\Admin\Controllers\AnalyticsController;
use App\Helpers\Helpers;
use App\Http\Requests\StoreRequest;
use App\Jobs\UpdateAttemptInRoutesParticipants;
use App\Jobs\UpdateResultParticipants;
use App\Jobs\UpdateRouteCoefficientParticipants;
use App\Models\Area;
use App\Models\Event;
use App\Models\Format;
use App\Models\Grades;
use App\Models\ListOfPendingParticipant;
use App\Models\MessageForParticipant;
use App\Models\OwnerPaymentOperations;
use App\Models\Place;
use App\Models\PlaceRoute;
use App\Models\ResultQualificationClassic;
use App\Models\ParticipantCategory;
use App\Models\ResultFinalStage;
use App\Models\ResultRouteQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\ResultSemiFinalStage;
use App\Models\Route;
use App\Models\RoutesOutdoor;
use App\Models\Set;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use stdClass;

class EventsController extends Controller
{
    /**
     * @throws \Exception
     */
    public function show(Request $request, $start_date, $climbing_gym, $title){
        $event_public_exist = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        $event_exist = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->first();
        $pre_show = false;
        $user_id = Auth()->user()->id ?? null;
        if($event_public_exist){
            $event = $event_public_exist;
        } else {
            if($request->is('admin/event/*')){
                $pre_show = true;
                $event = $event_exist;
            }
        }
        $is_show_button_list_pending = false;
        if($event_public_exist || $pre_show){
            $sets = Set::where('event_id', '=', $event->id)->orderBy('number_set')->get();
            foreach ($sets as $set){
                if($event->is_france_system_qualification){
                    $participants_event = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
                } else {
                    $participants_event = ResultQualificationClassic::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
//                    $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id','=',$user_id)->first();
                }
                $set->free = $set->max_participants - $participants_event;
                if($set->free <= 0){
                    $is_show_button_list_pending = true;
                }
                $a = $set->max_participants;
                $b = $set->free;

                if ($a === $b) {
                    $percent = 0;
                } elseif ($a < $b) {
                    $diff = $b - $a;
                    if($b != 0){
                        $percent = $diff / $b * 100;
                    } else {
                        $percent = 0;
                    }
                } else {
                    $diff = $a - $b;
                    if( $a != 0){
                        $percent = $diff / $a * 100;
                    } else {
                        $percent = 0;
                    }
                }
                $set->procent = intval($percent);
                $set->date = Helpers::getDatesByDayOfWeek($event_exist->start_date, $event_exist->end_date);
            }
            $owner = DB::table('admin_users')->find($event->owner_id);
            $event['climbing_gym_name_image'] = $owner->avatar;
            $is_show_button_final = boolval(ResultFinalStage::where('event_id', $event->id)->first());

            $is_add_to_list_pending = boolval(ListOfPendingParticipant::where('event_id', $event->id)->where('user_id', $user_id)->first());
            $list_pending = ListOfPendingParticipant::where('event_id', $event->id)->where('user_id', $user_id)->first();
            $is_show_button_semifinal = boolval(ResultSemiFinalStage::where('event_id', $event->id)->first());
            $sport_categories = User::sport_categories;
            if($event->is_france_system_qualification){
                $count_participants = ResultFranceSystemQualification::where('event_id','=',$event->id)->count();
                $participant = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('user_id', $user_id)->first();
            } else {
                $count_participants = ResultQualificationClassic::where('event_id','=',$event->id)->count();
                $participant = ResultQualificationClassic::where('event_id','=',$event->id)->where('user_id', $user_id)->first();
            }
            $message_for_participants = MessageForParticipant::where('event_id', $event->id)->first();
            $google_iframe = $this->google_maps_iframe($event->address.','.$event->city);
            $participant_products_and_discounts = $participant->products_and_discounts ?? null;
            $current_amount_start_price = OwnerPaymentOperations::current_amount_start_price_before_date($event);
            return view('welcome', compact(['current_amount_start_price','participant_products_and_discounts','message_for_participants','event','google_iframe','count_participants','is_show_button_list_pending','list_pending','is_add_to_list_pending', 'sport_categories', 'sets', 'is_show_button_final',  'is_show_button_semifinal']));
        } else {
            return view('errors.404');
        }
    }

    public function event_info_payment(Request $request, $start_date, $climbing_gym, $event_id)
    {
        $event = Event::find($event_id);
        return view('event.tab.payment_without_bill', compact('event'));
    }
    public function event_info_pay(Request $request, $start_date, $climbing_gym, $event_id)
    {
        $event = Event::find($event_id);
        $participant_products_and_discounts = $participant->products_and_discounts ?? null;
        $current_amount_start_price = OwnerPaymentOperations::current_amount_start_price_before_date($event);
        return view('event.tab.payment', compact(['current_amount_start_price','participant_products_and_discounts','event']));
    }
    public function get_participants(Request $request, $start_date, $climbing_gym, $title){
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event) {
            if($event->is_france_system_qualification){
                $table = 'result_france_system_qualification';
                $participants = User::query()
                    ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                    ->where($table.'.event_id', '=', $event->id)
                    ->select(
                        'users.id',
                        'users.middlename',
                        'users.city',
                        'users.team',
                        $table.'.gender',
                        $table.'.number_set_id',
                        $table.'.category_id',
                    )->get()->toArray();
            } else {
                $table = 'result_qualification_classic';
                $participants = User::query()
                    ->leftJoin($table, 'users.id', '=', $table.'.user_id')
                    ->where($table.'.event_id', '=', $event->id)
                    ->where($table.'.is_other_event', '=', 0)
                    ->select(
                        'users.id',
                        'users.middlename',
                        'users.city',
                        'users.team',
                        $table.'.gender',
                        $table.'.number_set_id',
                        $table.'.category_id',
                    )->get()->toArray();
            }
            if($event->is_input_set != 1){
                $days = Set::where('event_id', '=', $event->id)->select('day_of_week')->distinct()->get();
                $sets = Set::where('event_id', '=', $event->id)->get();
                $number_sets = Set::where('event_id', '=', $event->id)->pluck('id');
                foreach ($number_sets as $index => $set) {
                    if($event->is_france_system_qualification){
                        $sets[$index]->count_participant = ResultFranceSystemQualification::where('event_id', '=', $event->id)->where('number_set_id', $set)->count();
                    } else {
                        $sets[$index]->count_participant = ResultQualificationClassic::where('event_id', '=', $event->id)->where('is_other_event', 0)->where('number_set_id', $set)->count();
                    }
                    $sets[$index]->date = Helpers::getDatesByDayOfWeek($event->start_date, $event->end_date);
                    if($sets[$index]->count_participant == 0){
                        unset($sets[$index]);
                    }
                }
            } else {
                $days = null;
                $sets = null;
            }
            $index = 0;
            $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();

            foreach ($participants as $index_user => $user) {
                if ($index <= count($participants)) {
                    # Регистрация без сетов + без категории
                    # Регистрация без сетов + с категории
                    # Регистрация c сетов + без категории
                    # Регистрация c сетов + c категории

                    if($event->is_input_set == 1){
                        if($participants[$index]['category_id'] == 0){
                            $participants[$index_user] += ['category' =>  'Нет группы'];
                        } else {
                             $participants[$index_user] += ['category' => $categories[$participants[$index]['category_id']]];
                        }
                    } else {
                        # Регистрация с сетов + без категории
                        $set = $sets->where('id', '=', $user['number_set_id'])->where('owner_id', '=', $event->owner_id)->first();
                        if(isset($set->number_set)){
                            if($participants[$index]['category_id'] == 0){
                                $category = 'Нет группы';
                            } else {
                                $category = $categories[$participants[$index]['category_id']] ?? 'Нет группы';
                            }
                            $participants[$index_user]['category'] = $category;
                            $participants[$index_user]['number_set'] = $set->number_set;
                            $participants[$index_user]['time'] = $set->time . ' ' . trans_choice('somewords.' . $set->day_of_week, 10);
                        }
                    }
                }
                $index++;
            }
        } else {
            return view('errors.404');
        }
//        dd($days, $participants, $sets);
        return view('event.participants', compact(['days', 'event', 'participants', 'sets']));
    }

    public function get_qualification_classic_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
//        $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
        if($event){
            if(!$event->is_france_system_qualification){
//                $final_results = Participant::where('event_id', '=', $event->id)->where('active', '=', 1)->orderBy('points', 'DESC')->get()->toArray();
                $user_male_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('gender', '=', 'male')->where('active','=', 1)->where('category_id','!=', 0)->pluck('user_id')->toArray();
                $user_female_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('gender', '=', 'female')->where('active','=', 1)->where('category_id','!=', 0)->pluck('user_id')->toArray();
                $stats = new stdClass();
                if($event->is_open_team_result){
                    $user_team_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('active','=', 1)->pluck('user_id')->toArray();
                    $teams = User::whereIn('id', $user_team_ids)->where('team','!=', null)->distinct()->pluck('team')->toArray();
                    $stats->team = count($teams);
                    $result_team = array();
                }
                $female_categories = array();
                $male_categories = array();
                $stats->male = User::whereIn('id', $user_male_ids)->get()->count();
                $stats->female = User::whereIn('id', $user_female_ids)->get()->count();
                $result_male = array();
                $result_female = array();
                $categories = ParticipantCategory::where('event_id', $event->id)->get();
                foreach ($categories as $category) {
                    if($stats->male + $stats->female > 100){
                        $result_male_cache = Cache::remember('result_male_cache_'.$category->category.'_event_id_'.$event->id, 60 * 60, function () use ($event, $category) {
                            return ResultQualificationClassic::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                        });
                        $result_female_cache = Cache::remember('result_female_cache_'.$category->category.'_event_id_'.$event->id, 60 * 60, function () use ($event, $category) {
                            return ResultQualificationClassic::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                        });
                    } else {
                        $result_male_cache = ResultQualificationClassic::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                        $result_female_cache =  ResultQualificationClassic::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    }
                    $result_male[] = $result_male_cache;
                    $result_female[] = $result_female_cache;
//                    $result_male[] = Participant::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
//                    $result_female[] = Participant::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    $user_female = User::whereIn('id', $user_female_ids)->pluck('id');
                    $user_male = User::whereIn('id', $user_male_ids)->pluck('id');
                    $female_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_female)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                    $male_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_male)->where('event_id', '=', $event->id)->where('category_id', '=', $category->id)->get()->count();
                }
                if($event->is_open_team_result){
                    foreach ($teams as $team){
                        $result_team_cache = ResultQualificationClassic::get_list_team_and_points_participant($event->id, $team);
                        $result_team[$team] = $result_team_cache;
                    }
                    $result_team = ResultQualificationClassic::sorted_team_points($result_team);
                } else {
                    $result_team = null;
                    $teams = null;
                }
                $result_male_final = Helpers::arrayValuesRecursive($result_male);
                $result_female_final = Helpers::arrayValuesRecursive($result_female);
                $result = array_merge($result_male_final, $result_female_final);
                $stats->female_categories = $female_categories;
                $stats->male_categories = $male_categories;
                $categories = $categories->toArray();
            }
        } else {
            return view('errors.404');
        }
        return view('event.qualification_classic_results', compact(['event', 'result','teams', 'result_team',  'categories', 'stats']));
    }

    public function get_qualification_classic_global_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            if(!$event->is_france_system_qualification){
                $user_male_ids = ResultQualificationClassic::where(function($query) {
                    $query->where('active', 1)
                        ->orWhere(function($query) {
                            $query->where('active', 0)
                                ->where('is_other_event', 1);
                        });
                })
                    ->where('event_id', $event->id)
                    ->where('gender', 'male')
                    ->where('global_category_id', '!=', 0)
                    ->pluck('user_id')
                    ->toArray();
                $user_female_ids = ResultQualificationClassic::where(function($query) {
                    $query->where('active', 1)
                        ->orWhere(function($query) {
                            $query->where('active', 0)
                                ->where('is_other_event', 1);
                        });
                })
                    ->where('event_id', $event->id)
                    ->where('gender', 'female')
                    ->where('global_category_id', '!=', 0)
                    ->pluck('user_id')
                    ->toArray();

                $stats = new stdClass();
                if($event->is_open_team_result){
                    $user_team_ids = ResultQualificationClassic::where('event_id', '=', $event->id)->where('active','=', 1)->pluck('user_id')->toArray();
                    $teams = User::whereIn('id', $user_team_ids)->where('team','!=', null)->distinct()->pluck('team')->toArray();
                    $stats->team = count($teams);
                    $result_team = array();
                }
                $female_categories = array();
                $male_categories = array();
                $stats->male = User::whereIn('id', $user_male_ids)->get()->count();
                $stats->female = User::whereIn('id', $user_female_ids)->get()->count();
                $result_male = array();
                $result_female = array();
                $categories = ParticipantCategory::where('event_id', $event->id)->get();
                foreach ($categories as $category) {
                    if($stats->male + $stats->female > 100){
                        $result_male_cache = Cache::rememberForever('global_result_male_cache_'.$category->category.'_event_id_'.$event->id, function () use ($event, $category) {
                            return ResultQualificationClassic::get_global_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                        });
                        $result_female_cache = Cache::rememberForever('global_result_female_cache_'.$category->category.'_event_id_'.$event->id, function () use ($event, $category) {
                            return ResultQualificationClassic::get_global_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                        });
                    } else {
                        $result_male_cache = ResultQualificationClassic::get_global_sorted_group_participant($event->id, 'male', $category->id)->toArray();
                        $result_female_cache =  ResultQualificationClassic::get_global_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    }
                    $result_male[] = $result_male_cache;
                    $result_female[] = $result_female_cache;
//                    $result_male[] = Participant::get_sorted_group_participant($event->id, 'male', $category->id)->toArray();
//                    $result_female[] = Participant::get_sorted_group_participant($event->id, 'female', $category->id)->toArray();
                    $user_female = User::whereIn('id', $user_female_ids)->pluck('id');
                    $user_male = User::whereIn('id', $user_male_ids)->pluck('id');

                    $female_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_female)->where('event_id', '=', $event->id)->where('global_category_id', '=', $category->id)->get()->count();
                    $male_categories[$category->id] = ResultQualificationClassic::whereIn('user_id', $user_male)->where('event_id', '=', $event->id)->where('global_category_id', '=', $category->id)->get()->count();
                }
                if($event->is_open_team_result){
                    foreach ($teams as $team){
                        $result_team_cache = ResultQualificationClassic::get_global_list_team_and_points_participant($event->id, $team);
                        $result_team[$team] = $result_team_cache;
                    }
                    $result_team = ResultQualificationClassic::sorted_team_points($result_team);
                } else {
                    $result_team = null;
                    $teams = null;
                }
                $result_male_final = Helpers::arrayValuesRecursive($result_male);
                $result_female_final = Helpers::arrayValuesRecursive($result_female);
                $result = array_merge($result_male_final, $result_female_final);
                $stats->female_categories = $female_categories;
                $stats->male_categories = $male_categories;
                $categories = $categories->toArray();
            }
        } else {
            return view('errors.404');
        }
        return view('event.qualification_classic_global_results', compact(['event', 'result','teams', 'result_team',  'categories', 'stats']));
    }

    public function get_qualification_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes_amount = Grades::where('event_id', $event->id)->first()->count_routes;
            $routes = [];
            for($i = 1; $i <= $routes_amount; $i++){
                $routes[] = $i;
            }
            if($event->is_france_system_qualification){
                $result_each_routes = array();
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_france_system_qualification', $event->id, 'female', $category)->toArray();

                    $users_male2 = Event::get_france_system_result('result_france_system_qualification', $event->id, 'male', $category)->toArray();
                    $result_each_routes['female'][$category->id] = $users_female2;
                    $result_each_routes['male'][$category->id] = $users_male2;
                }
            }
        } else {
            return view('errors.404');
        }
        return view('event.france_system_qualification_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }

    public function get_semifinal_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes = array();
            for ($route = 1; $route <= $event->amount_routes_in_semifinal; $route++) {
                $routes[] = $route;
            }
            $result_each_routes = array();
            if($event->is_sort_group_semifinal){
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'female', $category)->toArray();
                    $users_male2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'male', $category)->toArray();
                    $result_each_routes['male'][$category->id] = $users_male2;
                    $result_each_routes['female'][$category->id] = $users_female2;
                }
            } else {
                $users_female2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'female')->toArray();
                $users_male2 = Event::get_france_system_result('result_semifinal_stage', $event->id, 'male')->toArray();
                $result_each_routes['male'] = $users_male2;
                $result_each_routes['female'] = $users_female2;
            }
        } else {
            return view('errors.404');
        }
        return view('event.france_system_semifinal_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }

    public function get_final_france_system_results(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if($event){
            $categories = ParticipantCategory::where('event_id', $event->id)->get()->toArray();
            $routes = array();
            for ($route = 1; $route <= $event->amount_routes_in_final; $route++) {
                $routes[] = $route;
            }
            $result_each_routes = array();
            if($event->is_sort_group_final) {
                foreach ($event->categories as $category) {
                    $category = ParticipantCategory::where('category', $category)->where('event_id', $event->id)->first();
                    $users_female2 = Event::get_france_system_result('result_final_stage', $event->id, 'female', $category)->toArray();
                    $users_male2 = Event::get_france_system_result('result_final_stage', $event->id, 'male', $category)->toArray();
                    $result_each_routes['male'][$category->id] = $users_male2;
                    $result_each_routes['female'][$category->id] = $users_female2;
                }
            } else {
                $users_female2 = Event::get_france_system_result('result_final_stage', $event->id, 'female')->toArray();
                $users_male2 = Event::get_france_system_result('result_final_stage', $event->id, 'male')->toArray();
                $result_each_routes['male'] = $users_male2;
                $result_each_routes['female'] = $users_female2;
            }
        } else {
            return view('errors.404');
        }
//        dd($result_each_routes, $routes);
        return view('event.france_system_final_results', compact(['event', 'categories', 'result_each_routes', 'routes']));
    }


    public function store(StoreRequest $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        $user = User::find($request->user_id);
        if(!$event || !$event->is_registration_state){
            return response()->json(['success' => false, 'message' => 'Ошибка регистрации'], 422);
        }
        if(!Helpers::valid_email($user->email)){
            return response()->json(['success' => false, 'message' => 'Ошибка регистрации, укажите существующий email в профиле'], 422);
        }
        $participant_categories = ParticipantCategory::where('event_id', '=', $request->event_id)->where('category', '=', $request->category)->first();
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
            }
            $participant = new ResultFranceSystemQualification;
        } else {
            $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            if($participant){
                return response()->json(['success' => false, 'message' => 'Ошибка регистрации'], 422);
            }
            $participant = new ResultQualificationClassic;
        }

        if($event->is_input_set != 1){
            $number_set = $request->number_set;
            $set = Set::where('number_set', $number_set)->where('event_id', $event->id)->first();
            $participant->number_set_id = $set->id;
        }
        if($event->is_auto_categories){
            $participant->category_id = 0;
        } else {
            $participant->category_id = $participant_categories->id;
        }

        $participant->event_id = $request->event_id;
        if($request->gender){
            $participant->gender = $request->gender;
        } else {
            $participant->gender = $user->gender;
        }
        $participant->user_id = $request->user_id;
        $participant->owner_id = $event->owner_id;
        $participant->active = 0;
        $participant->save();
        if($user){
            if($request->gender){
                $user->gender = $request->gender;
            }
            if($request->sport_category){
                $user->sport_category = $request->sport_category;
            }
            if($request->birthday){
                $user->birthday = $request->birthday;
            }
            $user->save();
        }

        if ($participant->save()) {
            if($user && $event && $participant){
                ResultQualificationClassic::send_main_about_take_part($event, $user, $participant);
            }
            return response()->json(['success' => true, 'message' => 'Успешная регистрация'], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }

    public function changeSet(Request $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if(!$event || !$event->is_registration_state){
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
        $set = Set::where('event_id',$event->id)->where('number_set', $request->number_set)->first();
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            $participants_event = ResultFranceSystemQualification::where('event_id','=',$event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
        } else {
            $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            $participants_event = ResultQualificationClassic::where('event_id','=', $event->id)->where('owner_id','=',$event->owner_id)->where('number_set_id', '=', $set->id)->count();
        }
        $free = $set->max_participants - $participants_event;
        if($free <= 0){
            return response()->json(['success' => false, 'message' => 'В выбранном сете нет мест'], 422);
        }
        $event = Event::find($request->event_id);
        $number_set = $request->number_set;
        $set = Set::where('number_set', $number_set)->where('event_id', $event->id)->first();
        $participant->number_set_id = $set->id;
        $participant->save();
        if ($participant->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено']);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }
    public function sendProductsAndDiscount(Request $request) {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
        }
        $participant->products_and_discounts = ['discount' => $request->discount, 'products' => $request->products, 'helper' => $request->helper];
        $participant->amount_start_price = intval($request->amount_start_price);
        $participant->save();
        if ($participant->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено']);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка регистрации'], 422);
        }
    }

    public function sendResultParticipant(Request $request) {

        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        if(!$event || !$event->is_send_result_state){
            return response()->json(['success' => false, 'message' => 'Регистрация была закрыта'], 422);
        }
        $event_id = $event->id;
        $user_id = $request->user_id;
        $format = $event->mode;
        $amount = $event->mode_amount_routes;
        $owner_id = $request->owner_id;
        # Если не дан доступ из админки к редактированию то запрещать повторную отправку
        if(!$event->is_access_user_edit_result){
            $participant_active = ResultQualificationClassic::where('user_id', '=', $user_id)->where('event_id', '=', $event_id)->first();
            if (!$participant_active){
                return response()->json(['success' => false, 'message' => 'Результаты уже были добавлены или отсутствует регистрация'], 422);
            }
        } else {
            $this->if_exist_result_update_point($event_id, $user_id);
        }
        $count_routes = Grades::where('event_id', $event_id)->first();
        if (!$count_routes){
            return response()->json(['success' => false, 'message' => 'По данном соревнование не найдены трассы'], 422);
        }
        # Проверяем что есть результат был отмечен, умножение происходит на 2 потому что из 3 результатов failed passed и flash два из них false
        # Не должно быть меньше этого, то есть если не отмечена хотя бы одна трасса она будет больше чем $count_routes * 2
        $amount_false = Event::validate_result($request->result);
        if($event->is_zone_show){
            # 3 - это flash 30 или redpoint 30 или zone 30
            $amount_state_passed_for_validate = 3;
        } else {
            # 2 - это flash 30 или redpoint 30
            $amount_state_passed_for_validate = 2;
        }
        if($amount_false > $count_routes->count_routes * $amount_state_passed_for_validate){
            return response()->json(['success' => false, 'message' => 'Необходимо отметить все трассы'], 422);
        }
        $gender = User::find($user_id)->gender;
        $data = array();
        foreach ($request->result as $result) {
            $status = $result[0]; # "failed-17-Боулдер"
            $bool = $result[1]; # "false"
            $grade = $result[2];  # "6C"
            $status_route_id_route_name = explode('-', $status); # ["failed", "17","Боулдер"]
            $route_id = $status_route_id_route_name[1];
            if (str_contains($status, 'flash') && $bool == "true") {
                $attempt = 1;
                $data[] = array('grade' => $grade, 'gender'=> $gender,'points' => 0, 'user_id'=> $user_id, 'event_id'=> $event_id, 'owner_id'=> $owner_id,'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($status, 'redpoint') && $bool == "true") {
                $attempt = 2;
                $data[] = array('grade' => $grade, 'gender'=> $gender,'points' => 0, 'user_id'=> $user_id, 'event_id'=> $event_id, 'owner_id'=> $owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($status, 'zone') && $bool == "true") {
                $attempt = 3;
                $data[] = array('grade' => $grade, 'gender'=> $gender,'points' => 0, 'user_id'=> $user_id, 'event_id'=> $event_id, 'owner_id'=> $owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
            if (str_contains($status, 'failed') && $bool == "true") {
                $attempt = 0;
                $data[] = array('grade' => $grade, 'gender'=> $gender, 'points' => 0, 'user_id'=> $user_id, 'event_id'=> $event_id, 'owner_id'=> $owner_id, 'route_id' => $route_id, 'attempt'=> $attempt);
            }
        }
        $final_data = array();

        # нужен будет провести аккуратный рефактор после внедрения формата скального фестиваля
        $final_data_only_passed_route = array();
        if($event->type_event){
            $points_for_mode_2 = 0;
            foreach ($data as $route){
                # Варианты форматов подсчета баллов
                $owner_route = RoutesOutdoor::where('grade','=',$route['grade'])->where('event_id','=', $event_id)->first();
                $value_route = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route['attempt'], $owner_route, $format, $event);

                # Формат все трассы считаем сразу
                $route['points'] = $value_route;
                $route['value'] = $value_route;
                $final_data[] = $route;
                $points_for_mode_2 += $value_route;
                if ($route['attempt'] != 0){
                    $final_data_only_passed_route[] = $route;
                }
            }
            # Формат 10 лучших считаем уже после подсчета, так как ценность трассы еще зависит от коэффициента прохождений
        } else {
            if($format == Format::ALL_ROUTE){
                UpdateRouteCoefficientParticipants::dispatch($event_id, $gender);
                $active_participant = ResultQualificationClassic::participant_with_result($event_id, $gender);
            }
            $points_for_mode_2 = 0;
            foreach ($data as $route){
                # Варианты форматов подсчета баллов
                $owner_route = Route::where('grade','=',$route['grade'])->where('event_id','=', $event_id)->first();
                $value_route = (new \App\Models\ResultRouteQualificationClassic)->get_value_route($route['attempt'], $owner_route, $format, $event);
                # Формат все трассы считаем сразу
                if($format == Format::ALL_ROUTE) {
                    $count_route_passed = ResultRouteQualificationClassic::counting_result($event_id, $route['route_id'], $gender);
//                (new \App\Models\EventAndCoefficientRoute)->update_coefficitient($route['event_id'], $route['route_id'], $route['owner_id'], $gender);
                    $coefficient = ResultRouteQualificationClassic::get_coefficient($active_participant, $count_route_passed);
                    $route['points'] = $coefficient * $value_route;
                    $points_for_mode_2 += $coefficient * $value_route;
                } else if($format == 1) {
                    $route['points'] = $value_route;
                    $route['value'] = $value_route;
                }
                $final_data[] = $route;
                if ($route['attempt'] != 0){
                    $final_data_only_passed_route[] = $route;
                }
            }
        }
        if($format == Format::ALL_ROUTE || $format == Format::ALL_ROUTE_WITH_POINTS){
            (new \App\Models\Event)->insert_final_participant_result($event_id, $points_for_mode_2, $user_id);
        }
        if($format == Format::N_ROUTE){
            usort($final_data_only_passed_route, function($a, $b) {
                return $a['points'] <=> $b['points'];
            });
            $points = 0;
            $lastElems = array_slice($final_data_only_passed_route, -$amount, $amount);
            foreach ($lastElems as $lastElem) {
                $points += $lastElem['points'];
            }
            $participant = ResultQualificationClassic::where('user_id', '=', $user_id)->where('event_id', '=', $event_id)->first();
            $participant->points = $points;
            $participant->active = 1;
            $participant->save();
        }
        $final_data = Helpers::remove_key($final_data, 'points');
        # Добавление json результатов для редактирования в админке
        $participant = ResultQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->first();
        $participant->result_for_edit = $final_data;
        $participant->save();

        $result_classic_for_edit = ResultRouteQualificationClassic::where('event_id', $event_id)->where('user_id', $user_id)->first();
        if($event->is_access_user_edit_result && $result_classic_for_edit){
            UpdateAttemptInRoutesParticipants::dispatch($event_id, $final_data);
            # Обновить категорию после изменение нового результата
            Event::force_update_category_id($event, $user_id);
        } else {
            $result = ResultRouteQualificationClassic::insert($final_data);

            # Обновить категорию если у него ее нет, к этому моменту у него есть результат, он full участник
            Event::update_category_id($event, $user_id);

            # Обновить места
            $participants = User::query()
                ->leftJoin('result_qualification_classic', 'users.id', '=', 'result_qualification_classic.user_id')
                ->where('result_qualification_classic.event_id', '=', $event_id)
                ->where('result_qualification_classic.active', '=', 1)
                ->select(
                    'users.id',
                )->get()->pluck('id');
            $categories = ParticipantCategory::where('event_id', '=', $event_id)->get();
            if ($event->is_sort_group_final) {
                foreach (['female', 'male'] as $gender) {
                    foreach ($categories as $category) {
                        $participants_for_update = ResultQualificationClassic::whereIn('user_id', $participants)
                            ->where('category_id', $category->id)
                            ->where('event_id', $event_id)
                            ->where('gender', $gender)
                            ->orderBy('points', 'DESC')
                            ->get();
                        ResultQualificationClassic::update_places_in_qualification_classic($event, $participants_for_update);
                    }
                }
            } else {
                foreach (['female', 'male'] as $gender){
                    $participants_for_update = ResultQualificationClassic::whereIn('user_id', $participants)->where('event_id', '=', $event_id)->where('gender', $gender)->orderBy('points', 'DESC')->get();
                    ResultQualificationClassic::update_places_in_qualification_classic($event, $participants_for_update);
                }
            }
//            foreach ($participants as $participant) {
//                Event::update_participant_place($event, $participant->id, $participant->gender);
//            }
//            ResultQualificationClassic::update_places_participant_in_qualification($event_id, $participants, $gender);
        }
//        Event::refresh_final_points_all_participant($event);
        UpdateResultParticipants::dispatch($event_id);
        Helpers::clear_cache($event);
        if ($result) {
            return response()->json(['success' => true, 'message' => 'Успешная внесение результатов', 'link' => $event->link], 201);
        } else {
            return response()->json(['success' => false, 'message' => 'ошибка внесение результатов'], 422);
        }
    }

    public function listRoutesEvent(Request $request, $start_date, $climbing_gym, $title) {
        $event = Event::where('start_date', $start_date)->where('title_eng', '=', $title)->where('climbing_gym_name_eng', '=', $climbing_gym)->where('is_public', 1)->first();
        if(!$event){
            return view('errors.404');
        }
        if($event->type_event){
            $grades = RoutesOutdoor::where('owner_id', '=', $event->owner_id)->where('event_id', '=', $event->id)->get();
            $view = 'outdoor-result-page';
        } else {
            $grades = Route::where('owner_id', '=', $event->owner_id)->where('event_id', '=', $event->id)->get();
            $view = 'result-page';
        }
        $areas = [];
        $area_images = [];
        $places = [];
        $sectors = [];
        $sector_fields = [];
        $routes = [];
//        dd($grades);
        foreach ($grades as $route){
            $route_class = new stdClass();
            if($event->type_event){
                $route_class->route_name = $route->route_name;
                $place = Place::find($route->place_id);
                $area = Area::find($route->area_id);
                $sector = PlaceRoute::find($route->place_route_id);
                $route_class->place = $place->name;
                $route_class->area = $area->name;
                $route_class->sector = $sector->name;

                if(!in_array($place->name, $places)){
                    $places[] = $place->name;
                }
                if(!in_array($area->name, $areas)){
                    $areas[] = $area->name;
                    $area_images[$area->name] = $area->image;
                }
                if(!in_array($sector->name, $sectors)){
                    $sectors[] = $sector->name;
                }
                $sector_fields[$sector->name] = array('area_name' => ucfirst($area->name),'place_name' => ucfirst($place->name),'description' => $sector->description,'name' => $sector->name,'image' => $sector->image, 'web_link' => $sector->web_link);
                $route_class->image = $route->image;
                $route_class->web_link = $route->web_link;
            } else {
                $route_class->color = $route->color;
                $route_class->text_color = Helpers::getContrastColor($route->color);
            }
            $route_class->grade = $route->grade;
            $route_class->count = $route->route_id;
            $routes[] = $route_class;

        }
        $user_id = Auth::user()->id;
//        dd($rock_images, $area_images);
        $result_route_qualification_classic_participant = ResultRouteQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
        if($result_route_qualification_classic_participant){
            $result_qualification_classic_participant = ResultQualificationClassic::where('event_id', $event->id)->where('user_id', $user_id)->first();
            $result_participant = $result_qualification_classic_participant->result_for_edit;
        } else {
            $result_participant = null;
        }
        array_multisort(array_column($routes, 'count'), SORT_ASC, $routes);
        return view($view, compact(['routes','places','areas','area_images','sectors','sector_fields','event', 'result_participant']));
    }

    public function sendAllResult(Request $request)
    {
        $event = Event::find($request->event_id);
        try {
            $details = array();
            $details['title'] = $event->title;
            $details['event_start_date'] = $event->start_date;
            $details['event_url'] = env('APP_URL').$event->link;
            $details['event_id'] = $event->id;
            if(env('APP_ENV') == 'prod'){
                Mail::to($request->email)->queue(new \App\Mail\AllResultExcelFIle($details));
            }

            return response()->json(['success' => true, 'message' => 'Успешная отправка'], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка'], 422);
        }
    }

    public function addToListPending(Request $request)
    {
        $event = Event::where('id', '=', $request->event_id)->where('is_public', 1)->first();
        $user = User::find($request->user_id);
        if(!$event){
            return response()->json(['success' => false, 'message' => 'Ошибка внесения в лист ожидания'], 422);
        }
        if (!$event->is_registration_state) {
            return response()->json(['success' => false, 'message' => 'Ошибка внесения в лист ожидания'], 422);
        }
        if (!Helpers::valid_email($user->email)) {
            return response()->json(['success' => false, 'message' => 'Нужен существующий email, так как мы не сможем отправить вам письмо об участии'], 422);
        }
        if (!$request->number_sets) {
            return response()->json(['success' => false, 'message' => 'Вы не выбрали сет'], 422);
        }
        $participant_categories = ParticipantCategory::where('event_id', '=', $request->event_id)->where('category', '=', $request->category)->first();
        if ($event->is_input_set != 1) {
            $list_pending = ListOfPendingParticipant::where('event_id', $request->event_id)->where('user_id', $request->user_id)->first();
            if (!$list_pending) {
                $list_pending = new ListOfPendingParticipant;
            }
            if ($event->is_auto_categories) {
                $list_pending->category_id = 0;
            } else {
                $list_pending->category_id = $participant_categories->id;
            }
            $list_pending->user_id = $request->user_id;
            $list_pending->event_id = $request->event_id;
            $list_pending->number_sets = $request->number_sets;
            $user = User::find($request->user_id);
            if($user){
                if($request->gender){
                    $user->gender = $request->gender;
                }
                if($request->sport_category){
                    $user->sport_category = $request->sport_category;
                }
                if($request->birthday){
                    $user->birthday = $request->birthday;
                }
                $user->save();
            }
            if ($list_pending->save()) {
                if($user && $event && $list_pending){
                    ResultQualificationClassic::send_main_about_list_pending($event, $user, $list_pending);
                }
                return response()->json(['success' => true, 'message' => 'Успешно']);
            }
        }
    }

    public function removeFromListPending(Request $request)
    {
        $list_pending = ListOfPendingParticipant::where('event_id', $request->event_id)->where('user_id', $request->user_id)->first();
        if ($list_pending->delete()) {
            return response()->json(['success' => true, 'message' => 'Успешное удалено']);
        }
    }
    public function cancelTakePartParticipant(Request $request)
    {
        if($request->event_id){
            $event = Event::find($request->event_id);
            if(!$event){
                return response()->json(['failed' => true, 'message' => 'Ошибка отмены регистрации']);
            }
            if($event->is_france_system_qualification){
                $participant = ResultFranceSystemQualification::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            } else {
                $participant = ResultQualificationClassic::where('user_id',  $request->user_id)->where('event_id', $request->event_id)->first();
            }
            if ($participant->delete()) {
                return response()->json(['success' => true, 'message' => 'Регистрация успешно отменена']);
            }
        }

    }


    public function google_maps_iframe($address){
        $zoom = 5000;
        $lng = 'ru';
        $src = 'https://www.google.ru/maps/embed?pb='.
            '!1m18'.
            '!1m12'.
            '!1m3'.
            '!1d'.$zoom.
            '!2d0'.
            '!3d0'.
            '!2m3'.
            '!1f0'.
            '!2f0'.
            '!3f0'.
            '!3m2'.
            '!1i1024'.
            '!2i768'.
            '!4f13.1'.
            '!3m3'.
            '!1m2'.
            '!1s0'.
            '!2s'.rawurlencode($address).
            '!5e0'.
            '!3m2'.
            '!1s'.$lng.
            '!2s'.$lng.
            '!4v'.time().'000'.
            '!5m2'.
            '!1s'.$lng.
            '!2s'.$lng;
        return $src;
    }

    public function if_exist_result_update_point($event_id, $user_id)
    {
        $result = ResultQualificationClassic::where('user_id', '=', $user_id)->where('event_id', '=', $event_id)->first();
        if($result){
            if(intval($result->points) > 0){
                $result->points = 0;
                $result->save();
            }
        }


    }
    public function index_analytics(Request $request, $start_date, $climbing_gym, $title)
    {
        $event = Event::where('start_date', $start_date)
            ->where('title_eng', '=', $title)
            ->where('climbing_gym_name_eng', '=', $climbing_gym)
            ->where('is_public', 1)
            ->first();
        if(!$event) {
            if (!$event->is_open_public_analytics) {
                return view('errors.404');
            }
            return view('errors.404');
        }

        $categories = ParticipantCategory::where('event_id', $event->id)->pluck('category', 'id')->toArray();
        if($event->type_event){
            $grades = RoutesOutdoor::where('event_id', $event->id)->pluck('grade', 'route_id')->toArray();
        } else {
            $grades = Route::where('event_id', $event->id)->pluck('grade', 'route_id')->toArray();
        }

        return view('event.analytics', compact(['event','categories', 'grades']));
    }
    public function get_analytics(Request $request)
    {
        $gender = $request->input('gender');
        $event_id = $request->input('event_id');
        $stats = Cache::rememberForever('result_'.$gender.'analytics_cache_event_id_'.$event_id, function () use ($event_id, $gender) {
            return AnalyticsController::get_stats_gender($event_id, $gender);
        });
        return response()->json([
            'routes' => $stats,
        ]);
    }
}
