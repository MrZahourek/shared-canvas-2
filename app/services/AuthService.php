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
include "../app/models/User.php";

// Initialize Database
$db = new Database();

// Get the JSON data sent from userEntry.js
$data = json_decode(file_get_contents("php://input"), true);

function authSession($db, $username, $password) {
    // 1. make sure the password and username match

    // 2. check the tokens
    $tokens = getTokens();
    if ($tokens->refresh != "") {
        // hash token
        $token_hash = hash('sha256', $tokens->refresh);

        // get user id from database
        $last_session = $db->runQuery(
            "select * from active_sessions where refresh_token_hash = ?",
            [$token_hash]
        )->fetch();

        // compare username thats getting authenticated to the one from session
        $last_user = User::findById($last_session["userID"], $db);

        if ($last_user["username"] == $username) {
            // the user is same and we can refresh the refresh token and make new access token
        }
        else {
            // the user is not the same and its time to generate new session
        }
    }
    else {
        // tokens are expired and new session has to be made
    }

    // session is ready
}

// # Creating user

if ($data["new user"]) {
    // create user

    // auth session
}
else {
    // logging in

    // auth session
}