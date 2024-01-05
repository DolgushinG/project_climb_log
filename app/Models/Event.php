<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

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

    public static function generation_route($owner_id, $amount_route){


        $i = $owner_id;
        $grades = array(
            # 10
            ['owner_id' => $i ,'grade' => '4', 'amount' => 0, 'value' => 100],
            ['owner_id' => $i ,'grade' => '5', 'amount' => 0, 'value' => 150],
            ['owner_id' => $i ,'grade' => '5+', 'amount' => 0, 'value' => 200],
            # 10
            ['owner_id' => $i ,'grade' => '6A', 'amount' => 0, 'value' => 250],
            ['owner_id' => $i ,'grade' => '6A+', 'amount' => 0, 'value' => 300],
            # 12
            ['owner_id' => $i ,'grade' => '6B', 'amount' => 0, 'value' => 350],
            ['owner_id' => $i ,'grade' => '6B+', 'amount' => 0, 'value' => 400],
            ['owner_id' => $i ,'grade' => '6C', 'amount' => 0, 'value' => 450],
            # 11
            ['owner_id' => $i ,'grade' => '6C+', 'amount' => 0, 'value' => 500],
            ['owner_id' => $i ,'grade' => '7A', 'amount' => 0, 'value' => 550],
            ['owner_id' => $i ,'grade' => '7A+', 'amount' => 0, 'value' => 600],
            # 7
            ['owner_id' => $i ,'grade' => '7B', 'amount' => 0, 'value' => 650],
            ['owner_id' => $i ,'grade' => '7B+', 'amount' => 0, 'value' => 700],
            ['owner_id' => $i ,'grade' => '7C', 'amount' => 0, 'value' => 750],
            ['owner_id' => $i ,'grade' => '7C+', 'amount' => 0, 'value' => 800],
            # 0
            ['owner_id' => $i ,'grade' => '8A', 'amount' => 0, 'value' => 850],

        );
        $count = 0;
        for($y = 1; $y <= 5; $y++){
            for($i = 0; $i <= 14; $i++){
                if ($count == $amount_route){
                    break;
                }
                $grades[$i]['amount'] += 1;
                $count++;
            }
        }
        DB::table('grades')->insert($grades);
    }

    public function participant()
    {
        return $this->hasOne(Participant::class);
    }

}
