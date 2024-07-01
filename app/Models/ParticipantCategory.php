<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class ParticipantCategory extends Model
{
    protected $table = 'participant_categories';
    public function participant()
    {
        return $this->belongsTo(ResultQualificationClassic::class);
    }

    public function participant_final_stage()
    {
        return $this->belongsTo(ResultFinalStage::class);
    }
    public function participant_semifinal_stage()
    {
        return $this->belongsTo(ResultSemiFinalStage::class);
    }

    public function getUserCategory($owner_id)
    {
        $event = Event::where('owner_id', '=', $owner_id)
            ->where('active', 1)->first();
        $categories = ParticipantCategory::whereIn('category', $event->categories)->where('event_id', $event->id)->pluck('category', 'id')->toArray();
        $categories[0] = 'Не определена';
        return $categories;
    }


    public static function get_increase_category($user_model, $format_transfer_category){

        $categories_for_increase = array();
        $categories = ['6A+','6B','6B+','6C', '6C+', '7A', '7A+', '7B', '7C', '7C+', '8A', '8A+', '8B+', '8C', '8C+', '9A'];
        for($i = array_search($format_transfer_category["От какой категории будет перевод"], $categories); $i < count($categories);$i++){
            $categories_for_increase[] = $categories[$i];
        }
//        ['Категория участника' => '0', 'Кол-во трасс для перевода'=> '2','В какую категорию переводить' => '1', 'От какой категории будет перевод'=> '6C'],

        switch ($format_transfer_category["Категория участника"]){
            case "1":
//                    if($user_model->grade == "6C"){
//                        if($user_model->attempt == 1 || $user_model->attempt == 2){
//                            dd(11);
//                        }
//                    }
                if(in_array($user_model->grade, $categories_for_increase)){

                    if($user_model->attempt == 1 || $user_model->attempt == 2){

                        return array('user_id' => $user_model->user_id, 'increase_category' => true, 'next_category' => "2");
                    } else {
                        return null;
                    }

                }
                break;
            case "2":
//                    if($user_model->grade == "6C"){
//                        if($user_model->attempt == 1 || $user_model->attempt == 2){
//                            dd($user_model);
//                        }
//                    }
                if(in_array($user_model->grade, $categories_for_increase)){

                    if($user_model->attempt == 1 || $user_model->attempt == 2){
                        return array('user_id' => $user_model->user_id, 'increase_category' => true, 'next_category' => "3");
                    } else {
                        return null;
                    }
                }
        }
    }
}
