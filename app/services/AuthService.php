<?php
/* --
** Here we send requests to get authentication of the user
 *
 * todo:
 * - new user maker
 * - check login
 * - token security
*/
include "../app/controllers/conn.php";
include "../app/services/TokenService.php";

$data = json_decode(file_get_contents("php://input"), true);

// # Login of user
if ($data["authType"] == "login") {
    // go check if there is restore token
}

// # New user
if ($data["authType"] == "newUser") {}