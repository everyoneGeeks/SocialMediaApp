<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;

class updateProfile extends Controller
{
  private $rules = [
    'apiToken'  => 'required|min:68|exists:users,apiToken',
    'name'      => '',
    'ghostName' => '',
    'email'     => 'email|unique:users',
    'photo'     => 'image',
    ];

  private $messages = [
    'apiToken.required' => 403,
    'apiToken.min'      => 405,
    'apiToken.exists'   => 400,

    'email.required'    => 403,
    'email.unique'      => 402,
    'email.email'       => 406,

    'photo.image'       => 407
  ];

  public function updateProfile(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    if($user->is_active == 0) return response()->json(['status'=>401]);

    if($request->name) $user->name = $request->name;
    if($request->ghostName) $user->ghostName = $request->ghostName;
    if($request->email) $user->email = $request->email;
    if($request->photo) $this->SaveFile($user, 'photo', 'photo', 'images/user');

    $user->save();

    return response()->json(['status'=>200]);
  }
}
