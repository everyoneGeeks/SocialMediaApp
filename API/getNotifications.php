<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\User;
use AnswerMe\Notification;
use AnswerMe\UserNotification;

class getNotifications extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'page'          => 'numeric|min:1'
  ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function getNotifications(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    $notifications = UserNotification::select(
                      'notifications.id AS notfID',
                      'users.id AS userID','users.name AS username', 'users.photo', 'users.ghostName',
                      'ghost_images.imgUrl AS gImage',
                      'notifications.created_at AS time', 'notifications.posts_id AS postID', 'notifications.type AS action', 'notifications.is_ghost',
                      'notify_users.is_seen'
                     )
                     ->leftJoin('notifications', 'notify_users.notifications_id', '=', 'notifications.id')
                     ->leftJoin('users', 'notifications.following_users_id', '=', 'users.id')
                     ->leftJoin('ghost_images', 'users.ghost_images_id', '=', 'ghost_images.id')
                     ->where('notify_users.users_id', $user->id)
                     ->orderBy('notifications.created_at', 'desc')
                     ->get();

    for($i = 0; $i < count($notifications); $i++){
      $notifications[$i]['time'] = Carbon::parse($notifications[$i]['time'])->timestamp;
      if($notifications[$i]['is_ghost'] == 0){
        unset($notifications[$i]['is_ghost']);
        unset($notifications[$i]['ghostName']);
        unset($notifications[$i]['gImage']);
      }else{
        $notifications[$i]['username'] = $notifications[$i]['ghostName'];
        $notifications[$i]['photo'] = $notifications[$i]['gImage'];
        unset($notifications[$i]['is_ghost']);
        unset($notifications[$i]['ghostName']);
        unset($notifications[$i]['imgUrl']);
      }
    }

    $dataCollection = collect($notifications)->forPage($request->page, 20);
    foreach ($dataCollection as $notf) { $notf->photo = asset($notf->photo); }

    if(Count($dataCollection) == 0) return response()->json(['status'=>300]);

    foreach ($dataCollection as $notf) {
      UserNotification::where('users_id', $user->id)
                      ->where('notifications_id', $notf->notfID)
                      ->update(['is_seen' => 0]);
    }

    return response()->json(['status'=>200, 'notifications'=>$dataCollection]);
  }
}
