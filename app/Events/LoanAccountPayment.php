<?php

namespace App\Events;

use App\User;
use App\Office;
use App\PaymentMethod;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanAccountPayment implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;

    public $office_id;
    public $msg;
    private $broadcasts_on;
    public function __construct($payload,$office_id,$user_id,$payment_method_id)
    {
     
        $office = Office::find($office_id)->name;
        $by = User::find($user_id)->full_name;
        $payment = PaymentMethod::find($payment_method_id)->name;
        $payload['msg'] = 'Repayment '. money($payload['amount'],2) .' at ' . $office .' by ' . $by. ' ['.$payment.'].';
        $this->data = $payload;
        $this->office_id = $office_id;
        $broadcast_ids = Office::find($office_id)->getUpperOfficeIDS();
        $broadcasts_on = [];
        foreach($broadcast_ids as $id){
            $broadcasts_on[] = new PrivateChannel('dashboard.notifications.' . $id);
            $broadcasts_on[] = new PrivateChannel('dashboard.charts.repayment.'.$id);


        }
        // $broadcasts_on[] = new PrivateChannel('dashboard.charts.repayment.'.$this->office_id);
        $this->broadcasts_on = $broadcasts_on;

        //Update session
        $date = $payload['date'];
        $list = collect(array_values(session('dashboard.repayment_trend')['labels']));
        $index = false;
        $list->map(function($value, $key) use($date, &$index){
            if ($value == $date) {
                $index = $key;
            }
        });
        
        if($index>=0){
            //Update repayments session;
            $repayment_trend = session('dashboard.repayment_trend');
            $expected_repayment = $repayment_trend['expected_repayment'];
            $expected_repayment_new_val = $expected_repayment[$index] + $payload['amount'];
            $expected_repayment[$index]  = (string) $expected_repayment_new_val;
            
            $repayment_trend['expected_repayment'] = $expected_repayment;
            session(['dashboard.repayment_trend'=>$repayment_trend]);

            //Update disbursement trend
            $disbursement_trend = session('dashboard.disbursement_trend');

            $repayments_interest = $disbursement_trend['repayment_interest'];
            $repayments_interest_new_val = $repayments_interest[$index] + $payload['summary']['interest_paid'];
            $repayments_interest[$index]  = (string) $repayments_interest_new_val;
            
            $repayments_principal = $disbursement_trend['repayment_principal'];
            $repayments_principal_new_val = $repayments_principal[$index] + $payload['summary']['interest_paid'];
            $repayments_principal[$index]  = (string) $repayments_principal_new_val;
            

        
            $disbursement_trend['repayment_interest'] = $repayments_interest;
            $disbursement_trend['repayments_principal'] = $repayments_principal;

            session(['dashboard.disbursement_trend'=>$disbursement_trend]);
        }
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
        return 'loan-payment';
    }
}
