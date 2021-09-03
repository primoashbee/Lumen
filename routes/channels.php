<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event    broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
Broadcast::channel('dashboard.charts.repayment.{office_id}', function ($user, $office_id) {
    $office_id = (int) $office_id;
    $office_list = $user->scopes(true);
    return in_array($office_id,$office_list) ? true :false;
    
});
Broadcast::channel('dashboard.charts.disbursement.{office_id}', function ($user, $office_id) {
    $office_id = (int) $office_id;
    $office_list = $user->scopes(true);
    
    return in_array($office_id,$office_list) ? true :false;
});
Broadcast::channel('dashboard.notifications.{office_id}', function ($user, $office_id) {
    $office_id = (int) $office_id;
    $office_list = $user->scopes(true);
    return in_array($office_id,$office_list) ? true :false;
});
Broadcast::channel('user.notification.{office_id}', function ($user, $user_id) {
    $user_id = (int) $user_id;
    return  $user->id == $user_id ? true :false;
});


// Broadcast::channel('group.channel.{office_id}', function ($user, $office_id) {
//     $office_id = (int) $office_id;
//     return in_array($office_id,session('office_list_ids')) ? true :false;
// });
