<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;

class updatePhone extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users',
    'phone'         => 'required|min:99999999|numeric|unique:users',
    ];

  private $messages = [
    'apiToken.exists'       => 400,
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,

    'phone.required'        => 403,
    'phone.min'             => 406,
    'phone.numeric'         => 406,
    'phone.unique'          => 402,
  ];

  public function updatePhone(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $ver_code = $this->verCode();

    $user = User::where('apiToken', $request->apiToken)->first();

    if($user->is_active == 0){ return response()->json(['status'=>401]); }

    $user->verificationCode = $ver_code;
    $user->tmpPhone = $request->phone;
    $user->save();

    $this->sendSMS($request->phone, $ver_code);
    $GLOBALS['email'] = $user->email;
     Mail::send('emails.send', ['content' => $ver_code], function ($message) {
            $message->from('answerme@magdsoft.com', 'My name');
            $message->subject('subject');
            $message->to($GLOBALS['email']);
            });

    return response()->json(['status'=>200]);

  }
}
