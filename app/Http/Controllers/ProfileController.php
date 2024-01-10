<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }
    public function index() {
        $user = User::find(Auth()->user()->id);

        return view('profile.main', compact(['user']));
    }
    public function getTabContentOverview() {
        $user = User::find(Auth()->user()->id);
        return view('profile.overview', compact(['user']));
    }
    public function getTabContentSetting() {
        $user = User::find(Auth()->user()->id);
        return view('profile.setting', compact('user'));
    }
    public function getTabContentEdit() {
        $user = User::find(Auth()->user()->id);
        return view('profile.edit-profile', compact(['user']));
    }

    public function getTabContentEvents() {
        $user_id = Auth()->user()->id;
        $events_id = Participant::where('user_id', '=', $user_id)->pluck('event_id');
        $events = Event::whereIn('id', $events_id)->get();
        foreach ($events as $event){
            $event['amount_participant'] = Participant::where('event_id', '=', $event->id)->get()->count();
            if(Participant::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->active){
                $status = "Внес результаты";
            }else{
                $status = "Необходимо добавить результаты";
            }
            $user_places = Participant::counting_final_place($event->id);
            if(empty($user_places)){
                $user_places_exist = 'Нет результата';
            } else {
                $user_places_exist = $user_places[$user_id];
            }
            $event['participant_active'] = $status;
            $event['user_place'] = $user_places_exist;
        }
        return view('profile.events', compact(['events']));
    }
    public function editChanges(Request $request) {
        $messages = array(
            'city.string' => 'Поле город нужно вводить только текст',
            'city.required' => 'Поле город обязательно для заполнения',
            'name.required' => 'Поле имя обязательно для заполнения',
            'salaryHour.required' => 'Поле оплата за час обязательно для заполнения',
            'salaryHour.numeric' => 'Поле оплата за час нужно вводить только цифры',
            'salaryRouteBouldering.numeric' => 'Поле оплата за трассу боулдеринг нужно вводить только цифры',
            'categories.required' => 'Укажите область накрутки, должна быть хотя бы одна область',
            'salaryRouteRope.numeric' => 'Поле оплата за трассу трудность нужно вводить только цифры',
            'contact.required' => 'Поле контакт для связи обязательно для заполнения',
        );
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'city_name' => 'required|string',
            'contact' => 'required',
            'salaryHour' => 'nullable|numeric',
            'salaryRouteBouldering' => 'numeric|nullable',
            'salaryRouteRope' => 'numeric|nullable',
            'categories' => 'required',
        ],$messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=>$validator->errors()->all()],422);
        }
        $user = User::find(Auth()->user()->id);
        $user->name = $request->name;
        $user->description = $request->description;
        $user->exp_level = $request->exp_level;
        $user->educational_requirements = $request->educational_requirements;
        $user->exp_local = $request->exp_local;
        $user->exp_national = $request->exp_national;
        $user->exp_international = $request->exp_international;
        $user->salary_hour = $request->salaryHour;
        $user->salary_route_rope = $request->salaryRouteRope;
        $user->salary_route_bouldering = $request->salaryRouteBouldering;
        $user->company = $request->company;
        $user->grade = $request->grade;
        $user->active_status = intval($request->active);
        $user->other_city = intval($request->otherCity);
        $user->city_name = $request->city_name;
        $user->all_time = intval($request->allTime);
        $user->telegram = $request->telegram;
        $user->instagram = $request->instagram;
        $user->contact = $request->contact;
        $not = [];
        foreach ($request->categories as $id => $x){
            $not[] = $id;
        }
        $notCategories = Category::whereNotIn('id', $not)->get();
        foreach($notCategories as $notCategory){
            $match = UserAndCategories::where('user_id','=',$user->id)->where('category_id','=',$notCategory->id)->get()->count();
            if($match) {
                UserAndCategories::where('user_id','=',$user->id)->where('category_id','=',$notCategory->id)->delete();
            }
        }
        foreach($request->categories as $id => $x){
            $userAndCategory = new UserAndCategories;
            $UserAndCategories = UserAndCategories::where('user_id','=',$user->id)->where('category_id','=',$id)->get()->count();
            if ($UserAndCategories === 0) {
                $userAndCategory->user_id = $user->id;
                $userAndCategory->category_id = $id;
                $userAndCategory->save();
            }
        }
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Ошибка сохранения'], 422);
        }
    }

}
