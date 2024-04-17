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
        $notifications = Notification::with('notificationUsers')->get();
        $data = [];
        $unreadCount = 0;

        foreach ($notifications as $notification) {
            $readStatus = $this->getReadStatus($notification);
            $notification['read'] = $readStatus;
            $data[] = $notification;
            $unreadCount += $this->incrementUnreadCount($notification, $readStatus);
        }

        $this->setData([
            'notifications' => $data,
            'unreadCount' => $unreadCount,
        ]);

        return $this->returnResponse();
    }

    private function getReadStatus($notification)
    {
        return $notification->type == 1
            ? $this->getGlobalReadStatus($notification)
            : $this->getUserSpecificReadStatus($notification);
    }

    private function getGlobalReadStatus($notification)
    {
        if($notification->notificationUsers->contains('user_id', auth()->id())){
            return 1;
        }
        $userNotify = new NotificationUser();
        $userNotify->user_id = auth()->user()->id;
        $userNotify->notification_id = $notification->id;
        $userNotify->read = 1;
        $userNotify->save();
        return 0;
    }

    private function getUserSpecificReadStatus($notification)
    {
        $userSpecificReadStatus = 0;

        foreach ($notification->notificationUsers as $user) {
            if ($user->user_id == auth()->id()) {
                $userSpecificReadStatus = $user->read;
                break;
            }
        }

        if ($userSpecificReadStatus == 0) {
            $userNotify =  NotificationUser::where('user_id', auth()->id())->where('notification_id', $notification->id)->first();
            $userNotify->read = 1;
            $userNotify->save();
        }

        return $userSpecificReadStatus;
    }

    private function incrementUnreadCount($notification, $readStatus)
    {
        return $readStatus == 0 ? 1 : 0;
    }
}
