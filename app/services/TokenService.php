<?php
/*
 * Here we manage and control tokens
 *
 * todo:
 * - auth token generation
 * - regen token generation
*/
include "../app/controllers/conn.php";

$data = json_decode(file_get_contents("php://input"), true);
$db = new Database();

function getTokens() {
    $result = json_decode('{ "access": "", "refresh": "" }');

    if (isset($_COOKIE["access_token"])) {}
    else {}

    if (isset($_COOKIE["refresh_token"])) {}
    else {}

    return $result;
}

function createToken($tokenType) {}

function refreshToken($tokenType) {}



//function generateToken($tokenType, $db = new Database()) {
//    if ($tokenType == "access") {
//        // generate access token and update the db
//    }
//    elseif ($tokenType == "refresh") {
//        // generate refresh token and update the db
//    }
//    else {
//        // generate both
//    }
//}
//
//function refreshToken($tokenType, $db = new Database()) {
//    if ($tokenType == "access") {
//        // set new value for expiration of access token and update the db
//        $newExpiration = time() + (60 * 25); // in 25 min
//        // update cookie
//
//        // update db
//    }
//    elseif ($tokenType == "refresh") {
//        // set new value for expiration of refresh token and update the db
//        $newExpiration = time() + (60 * 60 * 24 * 5); // in 5 days
//        $token = $_COOKIE["refresh token"];
//
//        // update cookie
//        setcookie("refresh token", $token, $newExpiration);
//
//        // update db
//        $hash = password_hash($token, PASSWORD_DEFAULT);
//        $sql = "update active_sessions set refresh_token_expire = :time where refresh_token_hash = :hash";
//        $stmt = $db->runQuery($sql, ['time' => $newExpiration, 'hash' => $hash]);
//
//    }
//    else {
//        // generate both
//    }
//}
