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

/**
 * Description of db_handler
 *
 * @author Vishal
 */
require_once __DIR__ . '/db_connect.php';

class DBHandler {

    //put your code here
    private $con;
    private $connectToDB;

    function __construct() {
        $this->connectToDB = new DBConnect();
        $this->con = $this->connectToDB->connect();
    }

    function getDriverById($driverID) {
        $stmt = $this->con->prepare("SELECT * FROM driver_locate WHERE dID=?");
        $driverData = array();
        $stmt->bind_param('i', $driverID);
        if ($stmt->execute()) {
            $stmt->bind_result(
                    $driverData['dID'], $driverData['drv_fireb_id'], $driverData['drv_fcm_id'], $driverData['latitude'], $driverData['longitude'], $driverData['available']);
            $stmt->fetch();
        }
        $stmt->close();
        return $driverData;
    }
	
	function getUserLocationByFirebaseId($userFirebId) {
		$stmt=$this->con->prepare("SELECT * FROM users WHERE user_fireb_id=?");
		$stmt->bind_param('s',$userFirebId);
		if ($stmt->execute()) {
			$stmt->bind_result(
                    $driverData['u_id'], $driverData['user_fireb_id'], $driverData['latitude'], $driverData['longitude']);
			$stmt->fetch();
					
		}
		$stmt->close();
		return $driverData;
	}

    function getDriverByFirebaseId($drvFirebId) {
        $resp = array();
        $resp['error'] = true;
        $stmt = $this->con->prepare("SELECT * FROM driver_locate WHERE drv_fireb_id=?");
        $driverData = array();
        $stmt->bind_param('s', $drvFirebId);
        if ($stmt->execute()) {
            $resp['error'] = false;
            $stmt->bind_result(
                    $driverData['dID'], $driverData['drv_fireb_id'], $driverData['drv_fcm_id'], $driverData['latitude'], $driverData['longitude'], $driverData['available']);
            $stmt->fetch();
            $resp['driver_data'] = $driverData;
        }
        $stmt->close();
        return $resp;
    }

    function getUserByFirebaseId($usrFirebId) {
        $resp = array();
        $resp['error'] = true;
        $stmt = $this->con->prepare("SELECT * FROM users WHERE user_fireb_id=?");
        $userData = array();
        $stmt->bind_param('s', $usrFirebId);
        if ($stmt->execute()) {
            $resp['error'] = false;
            $stmt->bind_result(
                    $userData['u_id'], $userData['user_fireb_id'], $userData['latitude'], $userData['longitude']);
            $stmt->fetch();
            
            $resp['user_data'] = $userData;
        }
        $stmt->close();
        return $resp;
    }

    //updated
    function newDriver($drvFirebId, $drvFcmId) {
        $response = array();
        $response['error'] = true;
        $response['error_msg'] = "Driver not registered.";
        //$response['driver_id'] = -1;
        $stmt = $this->con->prepare("INSERT INTO driver_locate(drv_fireb_id,drv_fcm_id)"
                . " VALUES(?,?)");
        $stmt->bind_param('ss', $drvFirebId, $drvFcmId);
        if ($stmt->execute()) {
            $response['error'] = false;
            $response['error_msg'] = "Driver registerd successfully.";
            //$response['driver_id'] = $this->con->insert_id;
        }
        $stmt->close();
        return $response;
    }

    private static function vincentyGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {

// convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
                pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    function getDriversByLatLong($lat, $long) {
        $response = array();
        $response['error'] = true;
        $driverList = array();
        $stmt = $this->con->prepare("SELECT * FROM driver_locate WHERE available=1");
        //$stmt->bind_param('i', 1);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($driver = $result->fetch_assoc()) {
                //6km
                $dist = $this->vincentyGreatCircleDistance($lat, $long, $driver['latitude'], $driver['longitude']);
                if ($dist <= 60000) {
                    $driver['distance'] = $dist;
                    array_push($driverList, $driver);
                }
            }
            $response['driver_list'] = $driverList;
            $response['error'] = false;
        }
        $stmt->close();
        return $response;
    }

    //updated
    function updateDriverLocation($drvFirebId, $lat, $long) {
        //echo "Lat= ".$lat;
        //mysqli_report(MYSQLI_REPORT_ERROR);
        $rspMsg = "Location failed to update.";
        $query = "UPDATE driver_locate SET latitude=?,longitude=? WHERE drv_fireb_id=?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('dds', $lat, $long, $drvFirebId);
        if ($stmt->execute()) {
            $rspMsg = "Location updated to Lat: " . $lat . ", Long: " . $long;
        }
        $stmt->close();
        return $rspMsg;
    }

    function updateUserLocation($usrFirebId, $lat, $long) {
        $rsp_msg = "Location failed to update.";
        $query = "UPDATE users SET latitude=?,longitude=? WHERE user_fireb_id=?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('dds', $lat, $long, $usrFirebId);
        if ($stmt->execute()) {
            $rsp_msg = "Location updated to Lat: " . $lat . ", Long: " . $long;
        }
        $stmt->close();
        return $rsp_msg;
    }

    function newUser($userFirebId) {
        $response = array();
        $response['error'] = true;
        $response['error_msg'] = "User not registered.";
        $response['user_id'] = -1;
        $stmt = $this->con->prepare("INSERT INTO users(user_fireb_id)"
                . "VALUES(?)");
        $stmt->bind_param('s', $userFirebId);
        if ($stmt->execute()) {
            $response['error'] = false;
            $response['error_msg'] = "User registerd successfully.";
            $response['user_id'] = $this->con->insert_id;
        }
        $stmt->close();
        return $response;
    }

    //updated
//    function onDriverAllotted($dID, $allotteeFcmId, $allotteeContactFcmId) {
//        $rsp_msg = "Driver not allotted.";
//        $query = "UPDATE driver_locate SET passen_fcm_id=?,passen_ctct_fcm_id=? WHERE dID=?";
//        $stmt = $this->con->prepare($query);
//        $stmt->bind_param('ssi', $allotteeFcmId, $allotteeContactFcmId, $dID);
//        if ($stmt->execute()) {
//            $rsp_msg = "Driver allotted.";
//        }
//        $stmt->close();
//        $this->updateDriverAvailability($dID, 0);
//        return $rsp_msg;
//    }
//
//    function onDriverReleased($dID) {
//        $this->onDriverAllotted($dID, NULL, NULL);
//        $this->updateDriverAvailability($dID, 1);
//        return "Driver Released.";
//    }

    function updateDriverAvailability($drvFirebId, $isAvailable) {
        $rsp_msg = "Could not set availabiliy.";
        $query = "UPDATE driver_locate SET available=? WHERE drv_fireb_id=?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $isAvailable, $drvFirebId);
        if ($stmt->execute()) {
            $rsp_msg = "Driver marked ";
                if ($isAvailable === 1) 
                    $rsp_msg.="available.";
                else
                    $rsp_msg.="busy.";
        }
        $stmt->close();
        return $rsp_msg;
    }

    function updateFcmToken($drvFirebID,$drvFcmId) {
        $respMsg="Token not updated.";
        $stmt = $this->con->prepare("UPDATE driver_locate SET drv_fcm_id=? WHERE drv_fireb_id=?");
        $stmt->bind_param('ss', $drvFcmId, $drvFirebID);
        if ($stmt->execute()) {
            $respMsg = "Token updated.";
        }
        $stmt->close();
        return $respMsg;
    }
    function getAllDrivers() {
        $response = array();
        $response['error'] = true;
        $driverList = array();
        $stmt = $this->con->prepare("SELECT * FROM driver_locate");
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($driver = $result->fetch_assoc()) {
                array_push($driverList, $driver);
            }
            $response['driver_list'] = $driverList;
            $response['error'] = false;
        }
        $stmt->close();
        return $response;
    }

    function getAllUsers() {
        $response = array();
        $response['error'] = true;
        $userList = array();
        $stmt = $this->con->prepare("SELECT * FROM users");
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($user = $result->fetch_assoc()) {
                array_push($userList, $user);
            }
            $response['user_list'] = $userList;
            $response['error'] = false;
        }
        $stmt->close();
        return $response;
    }

    function __destruct() {
        $this->connectToDB->disconnect();
    }

}
