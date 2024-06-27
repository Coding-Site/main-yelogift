<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;

class NotificationUserController extends Controller
{
    use APIHandleClass;
    function index()
    {
        $notifications = Notification::with('notificationUsers')
        ->where('type', 1)
        ->orWhere(function ($query) {
            $query->where('type', 0)
            ->where('user_id', Auth()->user()->id);
        })
        ->orderBy('created_at', 'desc')
        ->get();
        $readNotifications = [];
        $unreadNotifications = [];
        $unreadCount = 0;
        $flag = 0;
        foreach ($notifications as $notification) {
            foreach ($notification->notificationUsers as $notificationUser){
                if($notificationUser->user_id == Auth()->user()->id){
                    $flag = 1;
                    $readNotifications[] = $notification;
                    break;
                }
            }
            if($flag == 0){
                $unreadNotifications[] = $notification;
                $unreadCount++;
            }else{
                $flag = 0;
            }
        }

        $this->setData([
            'readNotifications' => $readNotifications,
            'unreadNotifications' => $unreadNotifications,
            'unreadCount'=> $unreadCount
        ]);

        return $this->returnResponse();
    }

    public function markAsRead(Request $request){
        $notifyUser = new NotificationUser();
        $notifyUser->notification_id = $request->notification_id;
        $notifyUser->user_id = auth()->user()->id;
        $notifyUser->read = 1;
        $notifyUser->save();

    }
}
