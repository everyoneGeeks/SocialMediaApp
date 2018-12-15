<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\Category;

class categories extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'lang'          => 'required|in:ar,en',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'lang.required'         => 403,
    'lang.in'               => 406,
  ];

  public function categories(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $name = 'name_' .$request->lang;
    $categories = Category::select('id AS categoryID', $name.' AS category', 'hex_color AS hexColor')->get();

    return response()->json(['status'=>200, 'categories'=>$categories]);
  }
}
