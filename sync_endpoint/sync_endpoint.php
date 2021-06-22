<?php
// print_r($_GET);
// print_r(array_keys($_POST));
$data = json_decode(base64_decode($_POST['data']), true);
// print_r($data);
$respones = array();
$respones['status'] = true;
echo json_encode($respones);
// print_r($data['categories']);
// die;
?>