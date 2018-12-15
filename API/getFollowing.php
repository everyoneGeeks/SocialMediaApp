<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;
use AnswerMe\Action;

class getFollowing extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    'page'          => 'numeric|min:1'
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function getFollowing(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();
    if($user->is_active == 0){ return response()->json(['status'=>401]); }

    $followers = Action::select('id AS userID', 'name', 'photo')
                      ->where('users_id', $user->id)->where('type', 'follow')
                      ->join('users', 'actions.target_users_id', '=', 'users.id')
                      ->orderBy('name')
                      ->paginate(20);
  foreach($followers as $follower){
    $follower['photo'] = asset($followers['photo']);
  }

    if($followers->items() == NULL) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'following'=>$followers->items() ]);
  }
}
