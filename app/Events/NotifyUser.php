<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotifyUser implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $notification;
    public function __construct($notification)
    {
        if($notification->by->id == 1){
            $from = $notification->by->firstname;
        }else{
            $from = $notification->by->firstname. ' '.$notification->by->lastname;
        }
        $data = [
            'link' => $notification->link,
            'msg'=> $notification->msg,
            'from'=>$from,
            'to'=>$notification->to,
            'seen'=> (int) $notification->seen,
            'created_at'=> $notification->created_at,
        ];
        $this->notification = $data;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.notification.'.$this->notification['to']);
    }
    public function broadcastAs()
    {
        return 'notify-user';
    }
}
