<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Notification::where('type', 1)->get();
        $this->setData($notifications);
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title'=>'required',
            'message'=>'required',
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $notification = new Notification;
        $notification->title = $request->title;
        $notification->message = $request->message;
        $notification->type = 1; //for all
        $notification->save();
        $this->setData($notification);
        $this->setMessage(__('translate.create_notification_success'));
        return $this->returnResponse();
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($notification_id)
    {
        $notification = Notification::find($notification_id);
        if(!$notification){
            $this->setMessage(__('translate.notification_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $notifyUser = NotificationUser::where('notification_id',$notification->id)->get();
        foreach($notifyUser as $notify){
            $notify->delete();
        }
        $notification->delete();
        $this->setMessage(__('translate.delete_notification_success'));
        return $this->returnResponse();
    }
}
