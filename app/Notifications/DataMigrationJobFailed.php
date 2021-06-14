<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;

class DataMigrationJobFailed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $event; 
    public function __construct($event)
    {
        $this->event =  $event;
        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['slack','mail'];
        // return ['mail'];
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
    //  */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    public function toSlack($notifiable)
    {
        $event = $this->event;
        $fields = [
            'Exception message' => $event->exception->getMessage(),
            // 'Job class' => $event->job->resolveName(),
            // 'Job body' => $event->job->getRawBody(),
            // 'Exception' => $event->exception->getTraceAsString(),
            // 'MessageBag' => $event->getException()
        ];
        // // $message = "Famous Hello World!";
        // // 
        // // return (new SlackMessage)
        // //         ->from('Ghost', ':ghost:')
        // //         ->to('#channel-name')
        // //         ->content('Fix service request by '.$message);
        return (new SlackMessage)
            // ->content('One of your invoices has been paid!')
            ->error()
            ->content('GGWP A job failed at '.config('APP_NAME'))
            ->attachment(function (SlackAttachment $attachment) use ($fields) {
                $attachment->title(['Error on Migration','GGWP'])->fields($fields);
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'=>'Test Tile',
            'description'=>'Test description',
        ];
    }
}
