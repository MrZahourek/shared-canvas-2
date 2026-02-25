<?php
/*
 * Here we manage and control tokens
 *
 * todo:
 * - auth token generation
 * - regen token generation
*/
include "../app/controllers/conn.php";


function generateToken($tokenType) {
    if ($tokenType == "auth") {
        // generate auth token and update the db
    }
    elseif ($tokenType == "regen") {
        // generate regen token and update the db
    }
    else {
        // generate both
    }
}
