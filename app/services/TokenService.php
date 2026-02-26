<?php
/*
 * Here we manage and control tokens
 *
 * todo:
 * - auth token generation
 * - regen token generation
*/

// token functions
function getToken($type) {
    $result = (object) ['success' => false, 'token' => null];

    if (isset($_COOKIE[$type . "_token"])) {
        $result->token =  $_COOKIE[$type . "_token"];
        $result->success = true;
    }
    return $result;
}
function refreshToken($type, $db) {
    $result = (object) ['success' => false, 'error' => null];

    $rawToken = getToken($type);
    if (!$rawToken["success"]) {
        $result->error = "token not found";
        die ($result);
    }

    $expiration = 0;
    if ($type == "access") { $expiration = time() + (60 * 25); }
    else { $expiration = time() + (3600 * 24 * 5); }

    setcookie($type . "_token", $rawToken["token"], $expiration);
    $db->runQuery(
        `update active_sessions set ${$type . "_token_expire"} = ? where ${$type . "_token_hash"} = ?`,
        [$expiration, hash('sha256', $rawToken["token"])]
    );

    $result->success = true;

    return $result;
}

function generateToken($type, $db) {
    $tokenCreated = false;
    while (!$tokenCreated) {
        // get new token
        $rawToken = bin2hex(random_bytes(32));
        $hashToken = hash('sha256', $rawToken);

        $expiration = 0;
        if ($type == "access") { $expiration = time() + (60 * 25); }
        else { $expiration = time() + (3600 * 24 * 5); }

        $sql = `select * from active_sessions where ${$type . "_token_hash"} = ?`;
        $check = $db->runQuery($sql, [$hashToken]) -> fetch();

        if (empty($check)) {
            setcookie($type . "_token", $rawToken, $expiration);
            $sql = "";
            $second_token = "";
            if ($type == "access") {
                $sql = "update active_sessions set access_token_hash = ? set access_token_expire = ? where refresh_token_hash = ?";
                $second_token = $_COOKIE["refresh_token"];
            }
            else {
                $sql = "update active_sessions set refresh_token_hash = ? set refresh_token_expire = ? where access_token_hash = ?";
                $second_token = $_COOKIE["access_token"];
            }

            $db->runQuery($sql, [$hashToken, $expiration, hash('sha256', $second_token)]);
            $tokenCreated = true;
        }
    }
    return true;
}
