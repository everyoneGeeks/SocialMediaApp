<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Hash;
use Validator;
use AnswerMe\User;

class changePassword extends Controller
{
  private $rules = [
    'tmpToken'      => 'required|min:68|exists:users',
    'newPassword'   => 'required|min:6',
    ];

  private $messages = [
    'tmpToken.required'     => 403,
    'tmpToken.min'          => 405,
    'tmpToken.exists'       => 400,

    'newPassword.required'  => 405,
    'newPassword.min'       => 406,
  ];

  public function changePassword(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('tmpToken', $request->tmpToken)->first();

    if($user->is_active == 0) return response()->json(['status'=>401]);

    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json(['status'=>200]);

  }
}
