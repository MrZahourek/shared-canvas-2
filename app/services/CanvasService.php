<?php
// app/services/CanvasService.php
require_once dirname(__DIR__) . "/controllers/conn.php";
require_once dirname(__DIR__) .  "/models/User.php";
require_once dirname(__DIR__) . "/models/Session.php";

$db = new Database();

// 1. Try to verify the Access Token
$userID = Session::verifyAccessToken($_COOKIE['access_token'] ?? null, $db);

// -> no access token ... Access Token failed or its missing ...Try to use the Refresh Token!
if (!$userID) {
    $refreshCookie = $_COOKIE['refresh_token'] ?? null;

    if ($refreshCookie) {
        // Run the refresh function! This checks the DB and generates TWO new tokens
        $newTokens = Session::refresh($refreshCookie, $db);

        if ($newTokens) {
            // Success! Give the browser the brand new tokens
            setcookie("access_token", $newTokens['access'], time() + (15 * 60), "/", "", false, true);
            setcookie("refresh_token", $newTokens['refresh'], time() + (7 * 24 * 60 * 60), "/", "", false, true);

            // Now that we have a valid new access token, grab the userID from it
            $userID = Session::verifyAccessToken($newTokens['access'], $db);
        }
    }
}

// 2.  if new access created

// 3: Success! We securely know who this is.
$user = User::findById($userID, $db);


// 4. user approved
$data = $data = json_decode(file_get_contents("php://input"), true);

if ($data["action"] == "init") {
    // 1. canvas config
    // 2. canvas latest snapshot
    // 3. canvas getEdits
    // 4. user data


}

else if ($data["action"] == "new edit") {}