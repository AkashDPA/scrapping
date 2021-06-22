<?php

require_once('phpQuery-onefile.php');
require_once('helper_methods.php');

$category_id = $argv[1];
$start_page_id = $argv[2];
$page_count = $argv[3];
$code = $argv[4];

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

$category = getCategory($category_id);
$pages = getPagesRanged($category_id, $start_page_id, $page_count);
// log_msg('category_start', count($pages_all), $category['id'] . ' ' . $category['name']);
// $category_start_time = time();

$multi_handler = curl_multi_init();
$requests = array();
// $curl_start = time();

foreach ($pages as $k => $page) {
    $requests[$k] = array();
    $requests[$k]['category'] = $category;
    $requests[$k]['page'] = $page;

    $requests[$k]['url'] = "http://hot-sex-tube.com/categories/" . $category['name'] . "/" . $page['url'];

    $requests[$k]['curl_handle'] = curl_init($requests[$k]['url']);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_TIMEOUT, 20);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_SSL_VERIFYPEER, false);

    curl_multi_add_handle($multi_handler, $requests[$k]['curl_handle']);
}

//Execute our requests using curl_multi_exec.
$stillRunning = false;
do {
    curl_multi_exec($multi_handler, $stillRunning);
} while ($stillRunning);

//Loop through the requests that we executed.
foreach ($requests as $k => $request) {
    $requests[$k]['http_code'] = curl_getinfo($request['curl_handle'], CURLINFO_HTTP_CODE);
    $requests[$k]['content'] = curl_multi_getcontent($request['curl_handle']);
    curl_close($requests[$k]['curl_handle']);
}

curl_multi_close($multi_handler);

// $m = memory_get_usage();
// log_msg('memory', 'memory after curl', $m);
// // echo ('mmey after curl: ' . ($m / 1000) . "kb  ");
// log_msg('time', 'curl time', $curl_start);

// $db_start = time();

foreach ($requests as $key => $request) {
    if ($request['http_code'] != 200) {
        not_success_url($request['url']);
        continue;
    }
    $links = getLinksArray($request['content'], $category);
    saveLinks($category, $links);
}

file_put_contents('sync_file.txt', $code . ",", FILE_APPEND);
exit();
// log_msg('category_end', ($i + 1),  $category['name']);
// mark_done('category', $category);

// $end_time = time();
// echo "time: " . $end_time - $start_time;
// echo PHP_EOL;
// $m = memory_get_peak_usage(); //Get the current occupied memory
// echo (($m / 1000) . "kb  ");
// print_r($requests);
