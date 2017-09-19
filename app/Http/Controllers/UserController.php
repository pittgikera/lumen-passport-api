<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    //
  }

  /**
  * Register new user
  *
  * @param $request Request
  */
  public function register(Request $request)
  {
    $this->validate($request, [
      'email' => 'required|email|unique:users',
      'password' => 'required'
      ]);

    $hasher = app()->make('hash');
    $email = $request->input('email');
    $password = $hasher->make($request->input('password'));
    $user = User::create([
      'email' => $email,
      'password' => $password,
      ]);

    $res['success'] = true;
    $res['message'] = 'Success register!';
    $res['data'] = $user;
    return response($res);
  }

  /**
  * Get user by id
  *
  * URL /user/{id}
  */
  public function get_user(Request $request, $id)
  {
    $user = User::where('id', $id)->get();
    if ($user) {
      $res['success'] = true;
      $res['user'] = $user;
      $res['message'] = 'User retreived successfully';

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find user!';

      return response($res);
    }
  }

  /**
  * When user success login will retrive callback as api_token
  */
  public function login_email(Request $request)
  {
    $this->validate($request, [
      'email' => 'required|email|unique:users',
      'password' => 'required'
      ]);

    $hasher = app()->make('hash');

    $email = $request->input('email');
    $password = $request->input('password');
    $login = User::where('email', $email)->first();

    if ( ! $login) {
      $res['success'] = false;
      $res['message'] = 'Your email or password incorrect!';
      return response($res);
    } else {
      if ($hasher->check($password, $login->password)) {
        $api_token = sha1(time());
        $create_token = User::where('id', $login->id)->update(['api_token' => $api_token]);
        if ($create_token) {
          $res['success'] = true;
          $res['api_token'] = $api_token;
          $res['message'] = 'Login successful';
          $res['user'] = $login;
          return response($res);
        }
      } else {
        $res['success'] = false;
        $res['message'] = 'You email or password incorrect!';
        return response($res);
      }
    }
  }

  /**
  * Send one time password to a registered phone number
  */
  public function send_otp(){
    $this->validate($request, [
      'phone' => 'required',
      'country_code' => 'required'
      ]);
    //check if number exists in db
    $phone = $request->input('phone');
    $country_code = $request->input('country_code');
    $user_check = User::whereColumn([
      ['country_code', '=', $country_code],
      ['phone', 'like', '%'.$phone],
      ])->first();

    if ( ! $user_check) {
      $res['success'] = false;
      $res['message'] = 'No user exists with that number';
      return response($res);
    } else {
      $client = new GuzzleHttp\Client();
        //post fields for twillio request
      $post_data = [
      'via' => 'sms',
      'phone_number' => $phone,
      'country_code'   => $country_code,
      'locale' => 'en',
      ];

      $res = $client->request('POST',
        'https://api.authy.com/protected/json/phones/verification/start?api_key='.env('TWILLIO_KEY', false),
        $post_data);

        //Send response back
      return response($res->getBody());
    }
  }


    /**
    * Verify code entered by user then send api token and user deatails
    */
    public function verify_otp(){
      $this->validate($request, [
        'phone' => 'required',
        'verification_code' => 'required',
        'country_code' => 'required'
        ]);

      $phone = $request->input('phone');
      $country_code = $request->input('country_code');
      $verification_code = $request->input('verification_code');

      $get_data = [
      'api_key' => env('TWILLIO_KEY', false),
      'phone_number' => $phone,
      'country_code'   => $country_code,
      'verification_code' => $verification_code,
      ];

      //verify if code is correct
      $res = $client->request('GET',
        'https://api.authy.com/protected/json/phones/verification/check',
        $get_data);

        //check response from twilio
      $twilio_response = $res->getBody();

      if ($twilio_response ->success) {
          //verification code correct
          //get user details matching the phone number used
        $user_check = User::whereColumn([
          ['country_code', '=', $country_code],
          ['phone', 'like', '%'.$phone],
          ])->first();

        if ( ! $user_check) {
            //user doesn't exist
          $resp['success'] = false;
          $resp['message'] = 'No user exists with that number';
          return response($res);
        } else {
            //user exist
          $api_token = sha1(time());
          $create_token = User::where('id', $user_check->id)->update(['api_token' => $api_token]);
          if ($create_token) {
            $resp['success'] = true;
            $resp['api_token'] = $api_token;
            $resp['message'] = 'Verification successful';
            $resp['user'] = $user_check;
            return response($resp);
          }
        }
      }else{
          //verification code incorrect
        return $twilio_response;
      }
    }
  }
