<?php
use Illuminate\Support\Facades\Auth;

// function user_details($field){
//     return Auth::guard('usersSecond')->user()->$field ? Auth::guard('usersSecond')->user()->$field : '';
// }
function user_details(){
    return Auth::guard('usersSecond')->user();
}