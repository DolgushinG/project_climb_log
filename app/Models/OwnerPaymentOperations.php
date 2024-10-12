<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OwnerPaymentOperations extends Model
{
    protected $table = 'owner_payment_operations';


    const EASY = 1;
    const HARD = 2;
    const DINAMIC = 3;

    public static function current_amount_start_price_before_date($event)
    {
        if($event->up_price){
            $condition = $event->up_price;
            $now = Carbon::today();
            $now->setTimezone('Europe/Moscow');
            foreach ($condition as $item){
                if(Carbon::parse($item['До даты']) > $now){
                    return $item['Цена'];
                }
            }
        }
    }

    public static function execute_payment($participant, $owner_id, $event, $amount_participant)
    {
        $payments = OwnerPayments::where('event_id', $participant->event_id)->first();
        if (!$payments) {
            $payments = new OwnerPayments;
            $payments->owner_id = $owner_id;
            $payments->event_id = $participant->event_id;
            $payments->event_title = $event->title;
        }
        $amount = OwnerPaymentOperations::where('event_id', $participant->event_id)->sum('amount');
        $payments->amount_for_pay = $amount;
        $payments->amount_participant = $amount_participant;
        $payments->amount_cost_for_service = Event::COST_FOR_EACH_PARTICIPANT;
        $payments->save();
    }

    public static function execute_payment_operations($participant, $owner_id, $amount_start_price, $amount_name = null)
    {
        $transaction = OwnerPaymentOperations::where('event_id', $participant->event_id)
            ->where('user_id', $participant->user_id)->first();
        if (!$transaction) {
            $transaction = new OwnerPaymentOperations;
            $transaction->owner_id = $owner_id;
            $transaction->user_id = $participant->user_id;
            $transaction->event_id = $participant->event_id;
        }
        $transaction->amount = Event::counting_amount_for_pay_participant($amount_start_price);
        if($amount_name){
            $transaction->type = $amount_name;
        }
        $transaction->save();
    }
}
