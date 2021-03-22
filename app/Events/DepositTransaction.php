<?php

namespace App\Events;

use App\Office;
use App\PaymentMethod;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\User;

class DepositTransaction implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $type;
    public $data;
    private $office_id;
    public $broadcasts_on;
    public function __construct($payload, $office_id, $user_id, $payment_method_id, $type)
    {
        $office = Office::find($office_id)->name;
        $by = User::find($user_id)->full_name;
        $payment = PaymentMethod::find($payment_method_id)->name;
        if ($type=="deposit") {
            $payload['msg'] = 'CBU Deposit ' . money($payload['amount'], 2) . ' at '. $office  . ' by ' . $by . ' [' . $payment . ']';
        }
        if ($type=="withdraw") {
            $payload['msg'] = 'CBU Withdrawal ' . money($payload['amount'], 2) . ' at '. $office  . ' by ' . $by . ' [' . $payment . ']';
        }
        if ($type=="interest_posting") {
            $payload['msg'] = 'CBU Interest Posted ' . money($payload['amount'], 2) . ' at '. $office  . ' by ' . $by . ' [' . $payment . ']';
        }
        $this->office_id = $office_id;
        $this->data = $payload;
        $this->type = $type;

        $broadcast_ids = Office::find($office_id)->getUpperOfficeIDS();
        $broadcasts_on = [];
        foreach($broadcast_ids as $id){
            $broadcasts_on[] = new PrivateChannel('dashboard.notifications.' . $id);
        }
        $this->broadcasts_on = $broadcasts_on;

        
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->broadcasts_on;
    }

    public function broadcastAs(){
        if($this->type=="deposit"){
           return 'cbu-deposit';
        }
        if($this->type=="withdraw"){
           return 'cbu-withdraw';
        }
        if($this->type=="interest_posting"){
           return 'cbu-interest-posting';
        }
    }
}
