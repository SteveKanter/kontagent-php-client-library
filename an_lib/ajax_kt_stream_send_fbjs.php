<?php
include_once 'kt_config.php';
$uuid = null;
$uid = null;
$st1 = null;
$st2 = null;
$st3 = null;

if(isset($_GET['uid']))
    $uid = $_GET['uid'];
if(isset($_GET['uuid']))
    $uuid = $_GET['uuid'];
if(isset($_GET['st1']))
    $st1 = $_GET['st1'];
if(isset($_GET['st2']))
    $st2 = $_GET['st2'];
if(isset($_GET['st3']))
    $st3 = $_GET['st3'];

$an->kt_stream_send_fbjs($uid, $uuid, $st1, $st2, $st3);

?>