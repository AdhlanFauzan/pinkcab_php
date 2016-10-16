<?php

/*
 * Copyright 2016 Lenovo.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__ . '/../include/db_handler.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoLoader();

//$app = new \Slim\Slim();
class DummyLogWriter {
  public function write($message, $level = null) {
    error_log((string) $message);
    return true;
  }
}
$app = new \Slim\Slim([
    'log.writer' => new DummyLogWriter()
]);

//get all drivers-working
$app->get('/drivers', function() {
    $db = new DBHandler();
    echoResponse($db->getAllDrivers(), 200);
});

//get all users-working
$app->get('/users', function() {
    $db = new DBHandler();
    echoResponse($db->getAllUsers(), 200);
});

$app->get('/location/user/:user_fireb_id',function($ufId) {
	$db=new DBHandler();
	echoResponse($db->getUserLocationByFirebaseId($ufId),200);
});
//get driver for lat,long-working
$app->get('/driver/lat=:lat/long=:long', function($lat,$long) {
    $db = new DBHandler();
    echoResponse($db->getDriversByLatLong($lat, $long), 200);
});

//get driver by id-working
$app->get('/driver/:dID', function($dID) {
    $db = new DBHandler();
    echoResponse($db->getDriverById($dID), 200);
});

//register a new driver-working-updated
$app->post('/register/driver', function () use ($app) {
    verifyParams(array('drv_fireb_id', 'drv_fcm_id'));
    $drvFirebId = $app->request->post('drv_fireb_id');
    $drvFcmId = $app->request->post('drv_fcm_id');
    $db = new DBHandler();
    echoResponse($db->newDriver($drvFirebId, $drvFcmId), 200);
});

//register a new user-working-updated
$app->post('/register/user', function () use ($app) {
    verifyParams(array('user_fireb_id'));
    $userFirebId = $app->request->post('user_fireb_id');
    $db = new DBHandler();
    echoResponse($db->newUser($userFirebId), 200);
});

//update driver location-working-updated
$app->put('/driver/location/:drvFirebId', function($drvFirebId) use ($app) {
    verifyParams(array('latitude', 'longitude'));
    $lat = doubleval($app->request->put('latitude'));
    $long = doubleval($app->request->put('longitude'));
    $db = new DBHandler();
    $res['result']=$db->updateDriverLocation($drvFirebId, $lat, $long);
    echoResponse($res, 200);
});

//update user location-working-updated
$app->put('/user/location/:usrFirebId', function($userFirebId) use ($app) {
    verifyParams(array('latitude', 'longitude'));
    $lat = doubleval($app->request->put('latitude'));
    $long = doubleval($app->request->put('longitude'));
    $db = new DBHandler();
    $res['result']=$db->updateUserLocation($userFirebId, $lat, $long);
    echoResponse($res, 200);
});

//allot a driver-working-updated
$app->put('/driver/:drv_fireb_id/allot', function ($drvFirebId) use ($app) {
    
    $db = new DBHandler();
    verifyParams(array('user_fireb_id'));
    
    $drvData=$db->getDriverByFirebaseId($drvFirebId);
    $userFirebId=$app->request->put('user_fireb_id');
    $data=$db->getUserByFirebaseId($userFirebId);
   
    if (!$data['error']) {
        include_once '../libs/fcm/FcmSend.php';
        include_once '../libs/fcm/Push.php';
        $fcmSend=new FcmSend();
        $push=new Push();
        
        $data['from_driver']=$drvData['driver_data']['drv_fireb_id'];
        $push->setTitle($userFirebId);
        $push->setData($data);
        
        $fcmSend->send($drvData['driver_data']['drv_fcm_id'], $push->getPush());
    }
    //send message to driver
    $newData['sent']=$data;
    $newData['result']=$db->updateDriverAvailability($drvFirebId, 0);
    echoResponse($newData, 200);
});

//release a driver-working-updated
$app->put('/driver/:drv_fireb_id/release', function ($drvFirebId) {
    $db = new DBHandler();
    $res['result']=$db->updateDriverAvailability($drvFirebId, 1);
    echoResponse($res, 200);
});

//update fcm token
$app->put('/driver/:drv_fireb_id/update-token', function ($drvFirebId) use ($app) {

    verifyParams(array('fcm_token'));
    $fcm=$app->request->put('fcm_token');
    $db=new DBHandler();
    $resp['result']=$db->updateFcmToken($drvFirebId, $fcm);
    echoResponse($resp, 200);
});
function verifyParams($fields) {
    $error = false;
    $errorFields = '';
    $params = $_REQUEST;
    $app = \Slim\Slim::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        parse_str($app->request()->getBody(), $params);
    }

    foreach ($fields as $field) {
        if (!isset($params[$field]) || strlen(trim($params[$field])) <= 0) {
            $error = true;
            $errorFields.=$field . ', ';
        }
    }

    if ($error) {
        //$app= \Slim\Slim::getInstance();
        $errorFields = "Required fields are missing: " . substr($errorFields, 0, -2);
        $res['error'] = $error;
        $res['error_msg'] = $errorFields;
        echoResponse($res, 400);
        $app->stop();
    }
}

function echoResponse($resp, $statusCode) {
    $app = \Slim\Slim::getInstance();
    $app->status($statusCode);
    $app->contentType('application/json');
    echo json_encode($resp);
}

$app->run();
