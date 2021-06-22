<?php


require_once('PHP-MySQLi-Database-Class-master/MysqliDb.php');
// spl_autoload_register("autoload");
// require_once('simple_html_sax/simple_html_sax.php');
// require_once('simple_html_sax.php');

function getPageArray($html)
{
    // $m=memory_get_usage ();//Get the current occupied memory
    // echo($m.'  ');
    $html = str_get_wrapper_content($html);

    $page_list_html = $html->find('ul.pages', 0)->outertext;
    // (String) $last_li_txt = $last_li;

    $html->clear();
    unset($html);

    // print_r($last_li_txt);
    $html_array = simplexml_load_string($page_list_html);


    // print_r(count($html_array->li));
    // die;
    // $m=memory_get_peak_usage ();//Get the current occupied memory
    // echo($m.'  ');
    // die;
    // $s_last_li = $last_li->prev_sibling()->innertext;
    // // if(strpos($last_li->plaintext,"Next")>-1 || strpos($last_li->plaintext,"next")>-1 ){
    // //     $last_li = $last_li->prev_sibling() ;
    // // }
    // // $last_li = $last_li->outertext;

    // $html->clear();
    // $last_li->clear();
    // unset($html);
    // $s_last_li = str_get_html($s_last_li);
    // $last_page_number = $s_last_li->find('a',0)->innertext;
    // $s_last_li->clear();
    // die;
    // $m=memory_get_usage ();//Get the current occupied memory
    // echo($m.'  ');

    // echo $ul->outertext;
    // foreach ($ul as $li) {
    //     echo $li->outertext.PHP_EOL;
    // }
    // $html = null;
    // $m=memory_get_usage ();//Get the current occupied memory
    // echo($m.'  ');
    // print_r($ul);
    $last_page_number = (array)$html_array->li[count($html_array->li) - 2]->a;
    unset($html_array);

    $last_page_number = $last_page_number[0];
    // var_dump($last_page_number[0]);
    // die;
    $response = array();
    for ($i = 1; $i <= (int)($last_page_number); $i++) {
        $response[] = "$i.html";
    }
    // print_r($response);
    // $m=memory_get_usage ();//Get the current occupied memory
    // echo($m.'  ');
    return $response;
}

function getPagesArray($content)
{

    $doc = phpQuery::newDocument($content);
    //$doc = $doc->find('body:first')->find('#wrapper:first')->find('#content:first')->find('ul.pages');//var_export($doc->find('body'), true);
    $doc = $doc->find('body:first #wrapper:first #content:first ul.pages:first'); //var_export($doc->find('body'), true);
    $lis = $doc->find('li > a');
    $hrefs  = array();
    foreach ($lis as $li) {
        $a = $li->textContent; //getAttribute('href');
        $hrefs[] = $a;
    }
    $start_page = $hrefs[0];

    if (count($hrefs) < 2)
        $end_page = $hrefs[0];
    else
        $end_page = $hrefs[count($hrefs) - 2];

    unset($doc);
    $reponse = array();
    for ($i = $start_page; $i <= $end_page; $i++) {
        $reponse[] = $i . '.html';
        # code...
    }

    // print_r($reponse); die;
    // // $var = "<?php\n\n\$text = $var_str;\n\n";
    // file_put_contents('filename.php', $hrefs);
    return $reponse;
}
// function ee($p){
//     echo $p->
// }

function getLinksArray($content, $category)
{
    $doc = phpQuery::newDocument($content);
    $doc = $doc->find('body:first .thumbs3:first'); //var_export($doc->find('body'), true);
    $tiles = $doc->find('div.ti');
    $links  = array();
    foreach ($tiles as $tile) {
        $link = array();

        $pic_a = $tiles->find('div.pic > a', $tile);
        $url = $pic_a->attr('href');

        $params = array();
        // if (!isset(parse_url($url)['query']))
        parse_str(parse_url($url)['query'], $params);

        $img = $tiles->find('div.pic > a > img', $tile);
        $img_src = $img->attr('src');

        $span = $tiles->find('div.pic > a > span', $tile);
        $title = $span->html();

        $desc = $tiles->find('div.descr', $tile, true);
        $duration = $desc->find('span.dleft > span.strong')->html();

        $desc_tags = $desc->find('p:first > a');
        $tags = array();
        foreach ($desc_tags as $key => $tag) {
            $href = $tag->getAttribute('href');
            $href = explode('/', $href);
            if (isset($href[2]))
                $tags[] = $href[2];
        }

        $link['tags'] = json_encode($tags);
        $link['duration'] = trim($duration);
        $link['title'] = trim($title);
        $link['img_src'] = $img_src;
        $link['u_id'] = array_key_exists('id', $params) ? $params['id'] : NULL;
        $link['url'] = array_key_exists('u', $params) ? $params['u'] : ""; // $params['u'];
        $link['category_id'] = $category['id'];

        $links[] = $link;
    }
    file_put_contents('temp.php', json_encode($links, JSON_PRETTY_PRINT));

    $tiles = null;
    $doc = null;
    unset($tiles);
    unset($doc);
    return $links;
}

function getCategoriesArray($content)
{
    $doc = phpQuery::newDocument($content);
    $doc = $doc->find('body:first .tlist1:first'); //var_export($doc->find('body'), true);
    $lis = $doc->find('li > a');
    $hrefs  = array(array());
    foreach ($lis as $li) {
        $a = $li->getAttribute('href');
        $explode = explode("/", $a);
        $hrefs[]['name'] = $explode[2];
    }
    // $hrefs = array_values(array_flip(array_flip($hrefs)));
    // // $var = "<?php\n\n\$text = $var_str;\n\n";
    // file_put_contents('filename.php', $hrefs);
    return $hrefs;
}
function save_categories($data)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    // $db->insertMulti('categories',$data);
    $db->disconnect();
    // print_r($db->last_query());
}

function getCategories($offset = '', $limit = 1)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $cats = $db->rawQuery('SELECT * FROM categories WHERE is_done = 0 ORDER BY id ASC');
    $db->disconnect();
    return $cats;
}

function getCategory($category_id)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $cats = $db->rawQueryOne('SELECT * FROM categories WHERE id = ' . $category_id);
    $db->disconnect();
    return $cats;
}

function getPages($category_id, $offset = '', $limit = 1)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $pages = $db->rawQuery('SELECT * FROM pages WHERE is_done = 0 AND category_id = ' . $category_id . ' ORDER BY id ASC');
    $db->disconnect();
    return $pages;
}

function getPagesRanged($category_id, $offset = '', $limit = 1)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $pages = $db->rawQuery('SELECT * FROM pages WHERE category_id = ' . $category_id . ' ORDER BY id ASC LIMIT ' . $offset . ', ' . $limit);
    $db->disconnect();
    return $pages;
}
function getRemCategories()
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $cats = $db->rawQuery('SELECT * FROM categories WHERE is_done = 0 AND name in ("whore") ORDER BY id ASC');
    $db->disconnect();
    return $cats;
}


function save_pages($category, $links)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $pages = array(array());
    foreach ($links as $key => $link) {
        $pages[] = ['category_id' => $category['id'], 'url' => $link];
    }
    // $db->insertMulti('pages',$pages);
    $db->disconnect();
    // print_r($db->last_query());
}


function getCategoryPages($category_id)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $pages = $db->rawQuery('SELECT * FROM pages WHERE is_done = 0 AND category_id = ' . $category_id . ' ORDER BY url ASC');
    $db->disconnect();
    return $pages;
}

function log_msg($p1, $p2 = '', $p3 = '')
{
    if (!file_exists("log.txt"))
        file_get_contents("log.txt", "");

    global $no_memory_log, $no_time_log, $category_start_time;
    $str = "";
    switch ($p1) {
        case 'category_start': {
                $str .= PHP_EOL;
                $str .= "category start : " . $p3 . PHP_EOL;
                $str .= "pages(" . $p2 . ") done : ";
            }
            break;
        case 'category_end': {
                $str .= PHP_EOL;
                $str .= "category end : " . $p2;
                $str .= "   total time: " . (time() - $category_start_time);
                $str .= PHP_EOL;
            }
            break;
        case 'categories_count': {
                $str .= PHP_EOL;
                $str .= "------------------------------" . PHP_EOL;
                $str .= "categories count : " . $p2 . PHP_EOL;
            }
            break;
        case 'pages_start': {
                $str .= $p3 . " ";
            }
            break;
        case 'pages_ends': {
                $str .= $p3 . " ";
            }
            break;
        case 'memory': {
                if ($no_memory_log)
                    break;
                $str .= $p2 . " ";
                $str .= ($p3 / 1000000) . "mb ;";
            }
            break;
        case 'time': {
                if ($no_time_log)
                    break;
                $str .= $p2;
                $str .= (time() - $p3) . "s ;";
            }
            break;

        default:
            $str .= $p1 . " ;";
            break;
    }
    if ($str != "")
        file_put_contents("log.txt", $str, FILE_APPEND);
    echo $str;
}

function mark_done($identifier, $data)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $queries = array();

    if ($identifier == "category") {
        $queries[] = "UPDATE categories SET is_done = 1 WHERE id = " . $data['id'];
        $queries[] = "UPDATE pages SET is_done = 1 WHERE category_id = " . $data['id'];
    }


    foreach ($queries as $query) {
        $db->rawQuery($query);
    }
    $db->disconnect();
}

function saveLinks($category, $links)
{
    // $db =  new MysqliDb('localhost', 'root', '', 'scrapper');
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    // $db->insertMulti('links', $links);
    $db->insertMultiImproved('links_new', $links);
    $db->disconnect();
}

function not_success_url($url)
{
    if (!file_exists("no_success_urls.txt"))
        file_put_contents("no_success_urls.txt", "");
    file_put_contents("no_success_urls.txt", $url . PHP_EOL, FILE_APPEND);
}

function runInBackground($cmd)
{
    if (substr(php_uname(), 0, 7) == "Windows") {
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        exec($cmd . " > /dev/null &");
    }
}

function stillRunning($code)
{
    $string = file_get_contents('sync_file.txt');
    $numbers = explode(",", $string);
    $code_numbers =  explode(",", $code);
    if (array_sum($numbers) != array_sum($code_numbers))
        return true;
    // if(strcmp($code, $string) != 0)
    //     return true;
    // file_put_contents("sync_file.txt", "");
    return false;
}

function getSyncData()
{
    global $dbCred;
    $db =  new MysqliDb($dbCred['host'], $dbCred['user'], $dbCred['pass'], $dbCred['db']);
    $response = array();
    $last_category_id = getLastSyncCat();
    if ($last_category_id > 0) {
        $response['categories'] = $db->rawQuery('SELECT id FROM categories WHERE is_done = 1 AND id > ' . $last_category_id . ' ORDER BY id ASC LIMIT 2');
    } else {
        $response['categories'] = $db->rawQuery('SELECT id FROM categories WHERE is_done = 1 ORDER BY id ASC LIMIT 2');
    }
    if (count($response['categories']) < 1)
        return 'empty';
    $response['categories'] = array_column($response['categories'], "id");
    $response['pages'] = $db->rawQuery('SELECT id FROM pages WHERE is_done = 1 AND category_id in ' . implodeCategories($response['categories']));
    $response['pages'] = array_column($response['pages'], "id");
    $response['links_new'] = $db->rawQuery('SELECT category_id, u_id, title, img_src, url, tags, duration FROM links_new WHERE category_id in ' . implodeCategories($response['categories']));

    setLastSyncCat($response['categories'][count($response['categories']) - 1]);
    $db->disconnect();
    return $response;
}

function getSyncData2(){
    
    $filename='database_backup_'.date('m_d_h_i').'.sql';

    $result=exec('mysqldump scrapper --password= --user=root --single-transaction >E:\\projects\\scrapping\\'.$filename,$output);
    var_dump($output);
}

function getLastSyncCat()
{
    $last_id = file_get_contents("sync/sync_cat_id.txt");
    $last_id = trim($last_id);
    if (is_numeric($last_id) && $last_id != "")
        return $last_id;
    return -1;
}

function setLastSyncCat($cat_id)
{
    print_r($cat_id);
    file_put_contents("sync/sync_cat_id.txt", $cat_id);
}

function setSyncLog($type, $msg)
{
    $msg .= "--------------------------------------------";
    $msg = $msg . PHP_EOL . PHP_EOL;
    if (!file_exists('sync/sync_log.txt'))
        file_put_contents("sync/sync_log.txt", $msg);
    else
        file_put_contents("sync/sync_log.txt", $msg, FILE_APPEND);
}

function implodeCategories($cat_ids)
{
    return "(" . implode(",", $cat_ids) . ")";
}
