<?php

require_once('phpQuery-onefile.php');
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

$all_categories = getCategories($offset, $limit);
// $urls = array();
// print_r($categories_all); die;


// $urls = array(
//     // 'http://hot-sex-tube.com/categories/office/1.html',
//     // 'http://hot-sex-tube.com/categories/office/1.html',
//     // 'http://hot-sex-tube.com/categories/office/1.html',
//     // 'http://hot-sex-tube.com/categories/office/1.html',
//     // 'http://hot-sex-tube.com/categories/office/1.html',
//     'http://hot-sex-tube.com/categories/office/1.html'
// );

//Loop through each URL.
for ($i = 0; $i < count($all_categories); $i++) {
    //     echo $i;
    //     echo PHP_EOL;
    //     $categories = array_slice($categories_all, $i, 5);
    $category = $all_categories[$i];
    $pages_all = getPages($category['id']);

    log_msg('category_start', count($pages_all), $category['id'] . ' ' . $category['name']);
    $category_start_time = time();
    for ($j = 0; $j < count($pages_all); $j += $page_jump) {
        //     echo $i;
        //     echo PHP_EOL;
        log_msg('pages_start', $j, ($j + $page_jump));
        $pages = array_slice($pages_all, $j, $page_jump);

        $multi_handler = curl_multi_init();
        $curl_start = time();
        $requests = array();

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

        $m = memory_get_usage();
        log_msg('memory', 'memory after curl', $m);
        // echo ('mmey after curl: ' . ($m / 1000) . "kb  ");
        log_msg('time', 'curl time', $curl_start);

        $db_start = time();

        foreach ($requests as $key => $request) {
            if ($request['http_code'] != 200) {
                echo $request['url'] . PHP_EOL;
                continue;
            }
            $links = getLinksArray($request['content'], $category);
            saveLinks($category, $links);
        }
        $m = memory_get_peak_usage(); //Get the current occupied memory
        log_msg('memory', 'peak memory', $m);
        log_msg('time', 'db time', $db_start);
        $pages = null;
        $requests = null;
        unset($pages);
        unset($requests);

        if($j%($page_jump*3) == 0)
            gc_collect_cycles();
    }
    log_msg('category_end', ($i + 1),  $category['name']);
    mark_done('category', $category);
    $pages_all = null;
    unset($pages_all);
    gc_collect_cycles();
}
// $end_time = time();
// echo "time: " . $end_time - $start_time;
// echo PHP_EOL;
// $m = memory_get_peak_usage(); //Get the current occupied memory
// echo (($m / 1000) . "kb  ");
// print_r($requests);
