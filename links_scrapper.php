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
$partial_jump = 5;
$wait_time = 20;
$sleep_between_cmd = 1.5;
$sleep_between_cmd_sec = $sleep_between_cmd * 1000000;
$dbCred = [
    'host' => "localhost",
    'user' => "root",
    'pass' => "",
    'db' => "scrapper"
];

$all_categories = getCategories($offset, $limit);
log_msg('categories_count', count($all_categories));
//Loop through each URL.
foreach ($all_categories as $category) {

    $pages_all = getPages($category['id']);

    log_msg('category_start', count($pages_all), $category['id'] . ' ' . $category['name']);
    $category_start_time = time();
    // --------------------------------------

    for ($i=0; $i < count($pages_all);) { 
        // exec("start php partial_scrapper.php ".$category['id']." ".$i." ".$page_jump." > temp.php &"); // no $output
        $start_i = $i;
        $code = "";
        file_put_contents("sync_file.txt", "");
        for ($j=0; $j < $partial_jump ; $j++) {
            $cmd ="php partial_scrapper.php ".$category['id']." ".$i." ".$page_jump." ".$i; // no $output
            // echo $cmd.PHP_EOL;
            usleep($sleep_between_cmd_sec);
            runInBackground($cmd);
            $code.=$i.",";
            $i = $i + $page_jump;
        }
        // echo 'yes'.PHP_EOL;
        
        
        $keep_running = true;
        $start_time = time();
        
        do {
            if((time() - $start_time) > $wait_time)
                $keep_running = false;
            sleep(4);
        } while (stillRunning($code) && $keep_running == true);
        // echo 'yes 2';
        // sleep(100);
        // die();
        log_msg('pages_ends', $start_i, $i);
        // mark_done('pages', [$start_i, $i]);
    }

    // --------------------------------------
    log_msg('category_end',  $category['id'],  $category['name']);
    mark_done('category', $category);
    $pages_all = null;
    unset($pages_all);
    gc_collect_cycles();
}