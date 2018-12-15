<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use AnswerMe\Mail\API\forgetPasswordMail;
use Validator;
use AnswerMe\User;
use Mail;

class forgetPassword extends Controller
{
  private $rules = [
    'email'         => 'required|email|exists:users',
    ];

  private $messages = [
    'email.required'        => 403,
    'email.email'           => 405,
    'email.exists'          => 400,
  ];

  public function forgetPassword(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $ver_code = $this->verCode();

    $user = User::where('email', $request->email)->first();
    if($user->is_active == 0){ return response()->json(['status'=>401]); }

    $user->verificationCode = $ver_code;
    $user->save();

   // \Mail::to($user)->send(new forgetPasswordMail($user));
     $GLOBALS['email'] = $user->email;
     Mail::send('emails.send', ['content' => $ver_code], function ($message) {
            $message->from('answerme@magdsoft.com', 'My name');
            $message->subject('subject');
            $message->to($GLOBALS['email']);
            });

    return response()->json(['status'=>200]);
  }
}
