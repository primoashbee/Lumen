<?php

namespace App\Listeners;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoanAccountPaymentListener implements ShouldBroadcast, ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    private $broadcasts_on;
    public $data;
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
        $this->broadcasts_on = $event->broadcasts_on;
        $this->data = $event->data;
    }
    public function broadcastOn()
    {
        return $this->broadcasts_on;

    }
    public function broadcastAs(){
        return 'loan-payment';
    }
}
