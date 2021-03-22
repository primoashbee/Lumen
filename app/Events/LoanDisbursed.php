<?php

namespace App\Events;

use App\Office;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoanDisbursed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    private $broadcasts_on;
    public function __construct($payload)
    {
        $this->data = $payload;
        
        $broadcast_ids = Office::find($payload['office_id'])->getUpperOfficeIDS();
        $broadcasts_on = [];
        foreach($broadcast_ids as $id){
            $broadcasts_on[] = new PrivateChannel('dashboard.notifications.' . $id);
            // $broadcasts_on[] = new PrivateChannel('dashboard.charts.disbursement.'.$id);
        }

        $this->broadcasts_on = $broadcasts_on;
    }
  
    public function broadcastOn()
    {
        return $this->broadcasts_on;
    }
  
    public function broadcastAs()
    {
        return 'loan-disbursed';
    }
}

