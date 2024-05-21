<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ResultQualificationClassic;
use App\Models\ResultFranceSystemQualification;
use App\Models\User;
use Illuminate\Http\Request;

class CropImageController extends Controller
{
    public function uploadCropImage(Request $request)
    {
        $event = Event::find($request->event_id);
        if($event->is_france_system_qualification){
            $participant = ResultFranceSystemQualification::where('user_id', Auth()->user()->id)->where('event_id', $request->event_id)->first();
        } else {
            $participant = ResultQualificationClassic::where('user_id', Auth()->user()->id)->where('event_id', $request->event_id)->first();
        }

        if (!file_exists('storage/images/bill/users/'.$participant->user_id.'/')) {
            mkdir('storage/images/bill/users/'.$participant->user_id.'/', 0777, true);
        }
        $folderPath = public_path('storage/images/bill/users/'.$participant->user_id.'/');
        $image_parts = explode(";base64,", $request->image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        if ($image_type == 'png'|| $image_type == 'jpg' || $image_type == 'jpeg')
        {
            $imageName = uniqid() . '.png';
            $imageFullPath = $folderPath.$imageName;
            file_put_contents($imageFullPath, $image_base64);
            $participant->bill = 'images/bill/users/'.$participant->user_id.'/'.$imageName;
            if($participant->save()){
                return response()->json(['success'=> true,'message' => 'Чек успешно приложен'], 200);
            }
        } else {
            return response()->json(['success'=> false, 'message' => 'Неверный формат файла'],422);
        }
    }
}
