<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;

class validateCode extends Controller
{
  private $rules = [
    'phone'         => 'min:99999999|numeric|exists:users',
    'email'         => 'email|exists:users',
    'apiToken'      => 'min:68|exists:users',
    'code'          => 'required|numeric|min:100000'
    ];

  private $messages = [
    'phone.exists'          => 400,
    'phone.min'             => 405,
    'phone.numeric'         => 405,

    'email.exists'          => 400,
    'email.email'           => 405,

    'apiToken.exists'       => 400,
    'apiToken.min'          => 405,

    'code.required'         => 403,
    'code.numeric'          => 405,
    'code.min'              => 405,
  ];

  public function validateCode(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    if($request->phone){
      //To verify phone on registeration
      $user = User::where('phone', $request->phone)->first();
      if($user->verificationCode != $request->code){ return response()->json(['status'=>401]); }
      $user->verificationCode = NULL;
      $user->is_verified = 1;
      $user->save();
      return response()->json(['status'=>200, 'apiToken'=>$user->apiToken]);
    }elseif($request->email){
      //A Step to reset password
      $user = User::where('email', $request->email)->first();
      if($user->verificationCode != $request->code){ return response()->json(['status'=>401]); }
      $user->tmpToken = str_random(70);
      $user->verificationCode = NULL;
      $user->save();
      return response()->json(['status'=>200, 'tmpToken'=>$user->tmpToken]);
    }elseif($request->apiToken){
      //To update phone
      $user = User::where('apiToken', $request->apiToken)->first();
      if($user->verificationCode != $request->code){ return response()->json(['status'=>401]); }
      $user->verificationCode = NULL;
      $user->phone = $user->tmpPhone;
      $user->tmpPhone = NULL;
      $user->save();
      return response()->json(['status'=>200]);
    }

  }
}
