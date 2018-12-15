<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Hash;
use Validator;
use AnswerMe\User;

class updatePassword extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    'oldPassword'   => 'required|min:6',
    'newPassword'   => 'required|min:6',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'oldPassword.required'  => 403,
    'oldPassword.min'       => 406,

    'newPassword.required'  => 403,
    'newPassword.min'       => 407,
  ];

  public function updatePassword(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    if($user->is_active == 0) return response()->json(['status'=>401]);

    if (!Hash::check($request->oldPassword, $user->password)) return response()->json(['status'=>408]);

    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json(['status'=>200]);
  }
}
