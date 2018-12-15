<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\Setting;
use AnswerMe\Phone;

class contactUs extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    ];

  private $messages = [
    'apiToken.required'  => 403,
    'apiToken.min'       => 405,
    'apiToken.exists'    => 400,
  ];

  public function contactUs(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}


    $email = Setting::find(1) ? Setting::find(1)->contact_email : NULL;
    $phoneObjects = Phone::all();
    $phones = NULL;
    foreach ($phoneObjects as $object) {$phones[] = $object->phone;}

    return response()->json(['status'=>200, 'email'=>$email, 'phones'=>$phones]);
  }
}
