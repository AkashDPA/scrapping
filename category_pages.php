<?php

require_once('phpQuery-onefile.php');
require_once('helper_methods.php');

//The URLs that we want to send cURL requests to.
$start_time = time();
$categories_all = getRemCategories();
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
$requests = array();

//Loop through each URL.
// for ($i = 0; $i < count($categories_all); $i += 5) {
//     echo $i;
//     echo PHP_EOL;
//     $categories = array_slice($categories_all, $i, 5);
    $multi_handler = curl_multi_init();
    // die(print_r($categories));
    $curl_start = time();
    foreach ($categories_all as $k => $category) {
        $requests[$k] = array();
        $requests[$k]['url'] = "http://hot-sex-tube.com/categories/" . $category['name'] . "/1.html";
        $requests[$k]['category'] = $category;

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
    echo ('mmey after curl: ' . ($m / 1000) . "kb  ");
    echo "time: ".(time()-$curl_start);

    $db_start = time();

    //var_dump the $requests array for example purposes.
    foreach ($requests as $key => $request) {
        if ($request['http_code'] != 200) {
            echo $request['url'] . ' ' . $request['category']['name'] . PHP_EOL;
            continue;
        }
        $links = getLinksArray($request['content']); //getPagesArray($request['content']);
        // save_pages($request['category'], $links);
    }
    $m = memory_get_peak_usage(); //Get the current occupied memory
    echo ("  peak: ".($m / 1000) . "kb  ");
    echo " db time: ".(time()-$db_start).PHP_EOL;

// }
$end_time = time();
echo '<pre>';
echo "total time: " . ($end_time - $start_time);
echo PHP_EOL;
$m = memory_get_peak_usage(); //Get the current occupied memory
echo (($m / 1000) . "kb  ");
// print_r($requests);
