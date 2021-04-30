<?php

namespace App\Listeners;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TestListener implements ShouldQueue, ShouldBroadcast
{
    // use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        
    }

    public function broadcastOn()
    {
        return new Channel('channel-name');
    }
    public function broadcastAs(){
        return 'bulk-disburse-loan';
    }
}
