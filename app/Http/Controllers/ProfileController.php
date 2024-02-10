<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\ResultParticipant;
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
            $active = Participant::where('event_id', '=', $event->id)->where('user_id', '=', $user_id)->first()->active;
            if($active){
                $users = Participant::get_places_participant_in_qualification($event->id, $user_id);
                $user_place = $users[$user_id];
                $status = "Внес результаты";
            }else{
                $status = "Необходимо добавить результаты";
                $user_place = 'Нет результата';
            }
            $event['participant_active'] = $status;
            $event['user_place'] = $user_place;
            $res_par = ResultParticipant::where('event_id', '=', $event->id)->where('user_id','=',$user_id)->get();
            $result = array();
            foreach ($res_par as $res){
                if (isset($result[$res['grade']])){
                    $result[$res['grade']] += 1;
                } else {
                    $result[$res['grade']] = 1;
                }
            }
            $event['amount_passed_grades'] = json_encode(array_values($result));
        }
        return view('profile.events', compact(['events']));
    }
    public function editChanges(Request $request) {
        $messages = array(
            'firstname.string' => 'Поле Имя нужно вводить только текст',
            'lastname.string' => 'Поле Фамилия нужно вводить только текст',
        );
        $validator = Validator::make($request->all(), [
            'firstname' => 'string',
            'lastname' => 'string',
        ],$messages);
        if ($validator->fails())
        {
            return response()->json(['error' => true,'message'=>$validator->errors()->all()],422);
        }
        $user = User::find(Auth()->user()->id);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->city = $request->city;
        $user->gender = $request->gender;
        $user->team = $request->team;
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => 'Успешно сохранено'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Ошибка сохранения'], 422);
        }
    }

}
