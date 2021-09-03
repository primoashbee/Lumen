<?php

namespace App\Events;

use App\User;
use App\Office;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ClientCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $client;
    public $data;
    public $office_id;
    public $by;
    private $broadcasts_on;
    public function __construct($client)
    {
        $this->client = $client;
        
        $this->office_id = $this->client->office_id;
        $this->by = User::find($this->client->created_by)->full_name;
        $office = Office::find($this->client->office_id)->name;
        $msg = $this->client->firstname.' '.$this->client->lastname.' (New Partner client) '.'created under '. $office. ' by '.$this->by;
        $this->data = $msg;
        
        $broadcast_ids = Office::find($this->office_id)->getUpperOfficeIDS();
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
        return 'client-created';
    }

}
