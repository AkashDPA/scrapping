<?php

require_once('phpQuery-onefile.php');
require_once('helper_methods.php');
die;
//The URLs that we want to send cURL requests to.
$start_time = time();
$categories = getCategories();

$urls = array(
    // 'http://hot-sex-tube.com/categories/office/1.html',
    // 'http://hot-sex-tube.com/categories/office/1.html',
    // 'http://hot-sex-tube.com/categories/office/1.html',
    // 'http://hot-sex-tube.com/categories/office/1.html',
    // 'http://hot-sex-tube.com/categories/office/1.html',
    'http://hot-sex-tube.com/categories/office/1.html'
);

//An array that will contain all of the information
//relating to each request.
$requests = array();


//Initiate a multiple cURL handle
$mh = curl_multi_init();

//Loop through each URL.
foreach($urls as $k => $url){
    $requests[$k] = array();
    $requests[$k]['url'] = $url;
    //Create a normal cURL handle for this particular request.
    $requests[$k]['curl_handle'] = curl_init($url);
    //Configure the options for this request.
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_TIMEOUT, 10);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_SSL_VERIFYPEER, false);
    //Add our normal / single cURL handle to the cURL multi handle.
    curl_multi_add_handle($mh, $requests[$k]['curl_handle']);
}

//Execute our requests using curl_multi_exec.
$stillRunning = false;
do {
    curl_multi_exec($mh, $stillRunning);
} while ($stillRunning);

//Loop through the requests that we executed.
foreach($requests as $k => $request){
    // //Remove the handle from the multi handle.
    // curl_multi_remove_handle($mh, $request['curl_handle']);
    // //Get the response content and the HTTP status code.
    $requests[$k]['content'] = curl_multi_getcontent($request['curl_handle']);
    // $requests[$k]['http_code'] = curl_getinfo($request['curl_handle'], CURLINFO_HTTP_CODE);
    // //Close the handle.
    // curl_close($requests[$k]['curl_handle']);
}
//Close the multi handle.
curl_multi_close($mh);

$m=memory_get_usage ();//Get the current occupied memory
echo($m.'  \n');
//var_dump the $requests array for example purposes.
foreach ($requests as $key => $request) {
    // $page_list = getPageArray($request['content']);
    /* create the parser */
    // $parser = xml_parser_create();
    // xml_set_element_handler($parser, "startElemHandler", "endElemHandler");
    // xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    
    // // read the contents of the links file
    // $strXML = $request['content'];// implode("",file('links.xml'));
    
    // // output each link
    // xml_parse($parser, $strXML);
    
    // // clean up - we're done
    // xml_parser_free($parser);
    // $requests[$key]['content'] = $page_list;


    // $s = getPagesArray($request['content']);
    $s = getCategoriesArray($request['content']);
    save_categories($s);
}
$end_time = time();
echo '<pre>';
echo $end_time-$start_time;
echo PHP_EOL;
$m=memory_get_peak_usage ();//Get the current occupied memory
echo(($m/1000)."kb  ");
// print_r($requests);


