<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;

class searchUsers extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    'keyword'       => 'required',
    'page'          => 'numeric|min:1'
  ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'page.numeric'          => 404,
    'page.min'              => 404,

    'keyword.required'      => 403,
  ];

  public function searchUsers(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();
    if($user->is_active == 0){ return response()->json(['status'=>401]); }

    $users = User::select('id AS userID', 'name', 'photo')
                 ->where('name', 'LIKE', '%'.$request->keyword.'%')
                 ->orderBy('name')
                 ->paginate(20);
  foreach($users as $user){
    $user['photo'] = asset($user['photo']);}

    if($users->items() == NULL) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'users'=>$users->items() ]);

  }
}
