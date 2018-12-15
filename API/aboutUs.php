<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\Setting;

class aboutUs extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    'lang'          => 'required|in:ar,en',
    ];

  private $messages = [
    'apiToken.required' => 403,
    'apiToken.min'      => 405,
    'apiToken.exists'   => 400,

    'lang.required'     => 403,
    'lang.in'           => 406,
  ];

  public function aboutUs(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $message = Setting::find(1);

    if(!$message) return response()->json(['status'=>200, 'message'=>NULL]);

    switch ($request->lang) {
      case 'ar':
        return response()->json(['status'=>200, 'message'=>$message->about_us_ar]);
        break;
      case 'en':
        return response()->json(['status'=>200, 'message'=>$message->about_us_en]);
        break;
    }//EO Switch
  }
}
