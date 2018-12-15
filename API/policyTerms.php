<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Session;
use Validator;
use AnswerMe\Setting;

class policyTerms extends Controller
{
  private $rules = [
    'lang'          => 'required|in:ar,en',
    ];

  private $messages = [
    'lang.required' => 403,
    'lang.in'       => 405,
  ];

  public function policyTerms(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $terms = Setting::find(1);

    if(!$terms) return response()->json(['status'=>200, 'terms'=>NULL]);

    switch ($request->lang) {
      case 'ar':
        return response()->json(['status'=>200, 'terms'=>$terms->policy_terms_ar]);
        break;
      case 'en':
        return response()->json(['status'=>200, 'terms'=>$terms->policy_terms_en]);
        break;
    }//EO Switch
  }//EO policyTerms
}//EO Class
