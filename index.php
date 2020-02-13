<?php

require_once 'App/api/controller/UsersApiConroller.php';
try {
    $api = new UsersApi();
    echo $api->run();
} catch (Exception $e) {
    if ($e->getMessage()) {
        echo json_encode(Array('result' => 'error', 'msg'=>$e->getMessage()));
    } else {
        echo json_encode(Array('result' => 'error'));
    }
}