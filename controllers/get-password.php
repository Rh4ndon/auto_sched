<?php
ini_get('display_errors');
ini_set('display_errors', 1);
include '../models/functions.php';


$id = $_GET['id'];
$user = getRecord('users', "id = $id");
if ($user['password'] == sha1(md5('123456'))) {
    $password = '123456';
    echo json_encode(['password' => $password]);
}
exit;
