<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Hash;
use Validator;
use AnswerMe\User;


class login extends Controller
{
  private $rules = [
    'phone'         => 'required|min:99999999|numeric|exists:users',
    'password'      => 'required|min:6',
    ];

  private $messages = [
    'phone.required'        => 403,
    'phone.min'             => 405,
    'phone.numeric'         => 405,
    'phone.exists'          => 400,
    'password.required'     => 403,
    'password.min'          => 406,
  ];

  public function login(Request $request){

    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::select('id','apiToken', 'name', 'phone', 'email', 'password', 'ghostName', 'ghost_images_id', 'photo', 'is_verified', 'is_active')
                ->where('phone', $request->phone)->first();
    

    if (!Hash::check($request->password, $user->password)) return response()->json(['status'=>400]);
    if($user->is_active == 0){ return response()->json(['status'=>401]); }
    if($user->is_verified == 0){ return response()->json(['status'=>300]); }

	if($user->photo !== null)$user->photo = asset($user->photo);
    else $user->photo = null;
    unset($user['id']);
    unset($user['password']);
    unset($user['is_verified']);
    unset($user['is_active']);

    return response()->json(['status'=>200, 'user'=>$user]);
  }
}
