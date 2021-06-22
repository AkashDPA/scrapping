<?php

require_once('helper_methods.php');

//The URLs that we want to send cURL requests to.
$no_memory_log = true;
$no_time_log = true;
$start_time = time();
$offset = 0;
$limit = 500;
$page_jump = 5;
$dbCred = [
    'host' => "localhost",
    'user' => "root",
    'pass' => "",
    'db' => "scrapper"
];
$sync_url = "http://localhost/sync_endpoint/sync_endpoint.php";

while (true) {
    sleep(1);
    $data = getSyncData2();
    die;
    if ($data == "empty") {

        setSyncLog('request', 'empty');
    } else {
        $data =json_encode($data);
        echo strlen($data);
        $data = ['data' =>  base64_encode($data)];
        // setSyncLog('request', $data);
        $ch = curl_init($sync_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Set HTTP Header for POST request 
        // curl_setopt(
        //     $ch,
        //     CURLOPT_HTTPHEADER,
        //     array(
        //         'Content-Type: application/json',
        //         'Content-Length: ' . strlen($data)
        //     )
        // );


        // execute!
        $response = curl_exec($ch);

        // close the connection, release resources used
        curl_close($ch);

        // do anything you want with your response
        setSyncLog('response', $response);
    }
    die;
}
