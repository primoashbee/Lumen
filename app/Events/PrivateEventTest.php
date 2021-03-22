<?php

namespace App\Events;

use App\Room;
use App\Office;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrivateEventTest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $msg;
    public $broadcasts_on;
    public function __construct($office_id)
    {
        $this->msg = 'Private office_id ihhh';
        $this->room_id = $office_id;
        $broadcast_ids = Office::find($office_id)->getUpperOfficeIDS();
        $broadcasts_on = [];
        foreach($broadcast_ids as $id){
            $broadcasts_on[] = new PrivateChannel('group.channel.' . $id);
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

        return $this->broadcasts_on;;
    }

    public function broadcastAs()
    {
        return 'suntukan-tayo';
    }
}
