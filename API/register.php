<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Hash;
use Carbon\Carbon;
use Session;
use Validator;
use AnswerMe\User;
use Mail;


class register extends Controller
{
  private $rules = [
    'name'          => 'required',
    'ghostName'     => 'required',
    'ghostImageId'  => 'required|exists:ghost_images,id',
    'phone'         => 'required|min:999999|numeric|unique:users',
    'email'         => 'required|email|unique:users',
    'password'      => 'required|min:6',
    ];

  private $messages = [
    'name.required'         => 403,
    'ghostName.required'    => 403,
    'ghostImageId.required' => 403,
    'ghostImageId.exists'   => 402,
    'phone.required'        => 403,
    'phone.min'             => 405,
    'phone.numeric'         => 405,
    'phone.unique'          => 401,
    'email.required'        => 403,
    'email.email'           => 406,
    'email.unique'          => 400,
    'password.required'     => 403,
    'password.min'          => 407,
  ];

  public function register(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $ver_code = $this->verCode();

    $user = new User;
    $user->apitoken = str_random(70);
    $user->name = $request->name;
    $user->ghostName = $request->ghostName;
    $user->ghost_images_id = $request->ghostImageId;
    $user->phone = $request->phone;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->created_at = Carbon::now()->toDateString();
    $user->verificationCode = $ver_code;
    $user->is_active = 1;
    $user->is_verified = 0;
    $user->save();

    $this->sendSMS($request->phone, $ver_code);
    $GLOBALS['email'] = $user->email;
      Mail::send('emails.send', ['content' => $ver_code], function ($message) {
            $message->from('answerme@magdsoft.com', 'My name');
            $message->subject('subject');
            $message->to($GLOBALS['email']);
            });

    return response()->json(['status'=>200]);

  }//EO register

}//EO Class
