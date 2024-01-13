<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    protected $casts = [
        'grade_and_amount' =>'json',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location',
        'document',
        'image',
        'city',
        'title',
        'subtitle',
        'climbing_gym_name',
        'description',
        'link',
        'count_routes',
        'active'
    ];

    public static function generation_route($owner_id,$event_id, $routes){
        $grades = array();
        foreach ($routes as $route){
            $grades[] = array('owner_id' => $owner_id ,'event_id' => $event_id, 'grade' => $route['Категория'], 'amount' => $route['Кол-во'], 'value' => $route['Ценность']);
        }
        DB::table('grades')->insert($grades);
    }

    public static function exist_events($owner_id){
        return boolval(Event::where('owner_id', '=', $owner_id)->where('active', '=', 1)->first());
    }
    public function participant()
    {
        return $this->hasOne(Participant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function translate_to_eng($text, $mode='eng'){
        $cyr = ['а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п', 'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П', 'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];
        $lat = ['a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p', 'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya', 'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P', 'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];
        if($mode == 'eng'){
            return str_replace($cyr, $lat, $text);
        } else {
            return str_replace($lat, $cyr, $text);
        }
    }

}
