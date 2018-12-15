<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;
use AnswerMe\Action;

class getBlocked extends Controller
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

  public function getBlocked(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    $blocked = Action::select('users.id AS userID', 'name', 'photo')
                     ->where('users_id', $user->id)
                     ->where('is_ghost', 0)
                     ->where('type', 'block')
                     ->leftJoin('users', 'actions.target_users_id', '=', 'users.id')
                     ->orderBy('name')
                     ->paginate(20);
  foreach($blocked as $block){
    $block['photo'] = asset($block['photo']);
  }

    if($blocked->items() == NULL) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'blocked'=>$blocked->items()]);
  }
}
