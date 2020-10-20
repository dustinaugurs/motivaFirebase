<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Userlog;
use Validator;
use Mail;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

//use App\Http\Controllers\API\Message;


class UserLogController extends BaseController
{
    // public Database $database;
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $reference = $this->database->getReference('Users');

        // $value = $reference->getValue();
        // print_r($value);
    }

    public function userLogstore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'deviceID' => 'required',
            'email' => 'required',
            'username'=> 'required'
        ]);
        try{
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $userlogdata = Userlog::create($input);
            return $this->sendResponse($userlogdata->toArray(), 'Userlog created successfully.');

    } catch(\Illuminate\Database\QueryException $e){
        $errorCode = $e->errorInfo[1];
        $errorMessage = $e->errorInfo[2];
            if($errorCode == '1062'){
                return $this->sendError($errorMessage, $errorCode); 
            }
    }
        
    }


//----start-Send-Notification-----
public function sendNotification(Request $request)
    {  
        $data = array('name'=>"Motiva");
        Mail::send('emails.reminder', $data, function($message) {
            $message->to('augurstest@gmail.com', 'AutoloadCSV')->subject
               ('Motiva');
            $message->from('deepak@gmail.com','Motiva');
         });
        $file = include(__DIR__.'/Message.php');
        $users = Userlog::all();
        $device = [];
        foreach($users as $user){
            $device[] = $user->email;
        }
        
        //echo '<pre>'; print_r(count($message));
        $random_keys=array_rand($message,20);
        $bodyMessage = $message[$random_keys[0]]; 
        $reference = $this->database->getReference('Users');
        $alldevices = $reference->getValue();
        //echo '<pre>'; print_r($alldevices); die;
        $devicedata = array(); $data = array();
        foreach($alldevices as $value){
            $devicedata[] = $value; 
                 }
                 //echo '<pre>'; print_r($devicedata); die;
         foreach($devicedata as $device){
            $data[] = $device['deviceID']; 
         }        
       // echo '<pre>'; print_r($data);
      
    $url = "https://fcm.googleapis.com/fcm/send";
    $token = $data; // your device token
    $serverKey = 'AAAAjMdxBIQ:APA91bHv0gl7lJMAoU1izH_w4cDsdIJN9Q0GqhuXYzzpS2v-0UzvQN956Cq732VBNG-KOF78bTv200_FwEYOpjxEtaNzB0KsUOyddjdFcwF_SYZA7X_6CpHfGrVH3T1DanG7fGYmVPx_';   // Your server token for FCM project
    $title = "Motiva Today Quote";
    $body = $bodyMessage;
    $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('registration_ids' => $token, 'notification' => $notification,'priority'=>'high');
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
        
    }
//----end-Send-Notification-----

}
