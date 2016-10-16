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
 * Description of FcmSend
 *
 * @author Vishal
 */
class FcmSend {

    //put your code here

    function send($to, $message) {
        $fields = array(
            'to' => $to,
            'data' => $message
        );
        return $this->sendPushNotif($fields);
    }

    function sendPushNotif($fields) {
        require_once __DIR__ . '/../../include/config.php';

        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $cURLCon = curl_init();
        curl_setopt($cURLCon, CURLOPT_URL, $url);
        curl_setopt($cURLCon, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($cURLCon, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURLCon, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cURLCon, CURLOPT_POSTFIELDS, json_encode($fields));

        $res = curl_exec($cURLCon);

        if (!$res) {
            die('cURL failed: ' . curl_errno($cURLCon));
        }
        curl_close($cURLCon);

        return $res;
    }

}
