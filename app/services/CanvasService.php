<?php
// app/services/CanvasService.php
require_once dirname(__DIR__) . "/controllers/conn.php";
require_once dirname(__DIR__) .  "/models/User.php";
require_once dirname(__DIR__) . "/models/Session.php";
require_once dirname(__DIR__) . "/models/Canvas.php";

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

// 2. THE BOUNCER. If $userID is STILL false, kick them out cleanly!
if (!$userID) {
    echo json_encode(["success" => false, "error" => "unauthorized"]);
    exit;
}

//3.  Success! We securely know who this is.
$user = User::findById($userID, $db);


// 4. user approved
$data = $data = json_decode(file_get_contents("php://input"), true);
$result = [];

if ($data["action"] == "init") {
    // 1. canvas config
    // 2. canvas latest snapshot
    // 3. canvas getEdits
    // 4. user data
    $canvasData = Canvas::getInit($data["canvasName"], $db);
    $userData = $user->getInit();

    $result = [
        "success" => true,
        "canvas_config" => $canvasData["config"],
        "canvas_snapshot" => $canvasData["snapshot"],
        "canvas_recent_edits" => $canvasData["recent_edits"],
        "canvas_last_edit_id" => $canvasData["snapshot_last_id"],
        "username" => $userData["username"],
        "user_last_edit_at" => $userData["last_edit_at"]
    ];
}

else if ($data["action"] == "get edits") {
    $canvas = $data["canvasName"];
    $last_edit_id = $data["last_edit_id"];

    $query = $db->runQuery("select editID, x, y, color from edit_history where editID > ? and canvas_name = ? order by editID asc", [$last_edit_id, $canvas])->fetchAll(PDO::FETCH_ASSOC);

    $result = [
        "success" => true,
        "edits" => $query
    ];
}

else if ($data["action"] == "new edit") {
    $edit_x = $data["x"];
    $edit_y = $data["y"];
    $edit_color = $data["color"];

    // 1. Get the current time in total seconds
    $now = time();

    // 2. Get user's last edit in total seconds (If NULL, set to 0 so they can draw instantly)
    $lastEditTime = $user->last_edit_at ? strtotime($user->last_edit_at) : 0;

    // 3. Calculate total seconds passed
    $diffSeconds = $now - $lastEditTime;

    // 4. Get the required wait time in seconds (JS sends ms, so divide by 1000)
    $waitSeconds = intval($data["wait"] / 1000);

    // 5. Compare!
    if ($diffSeconds >= $waitSeconds) {

        // ---> TODO: Insert the pixel into the database here! <---
        $canvas = $data["canvasName"];
        $db -> runQuery("insert into edit_history(canvas_name, x, y, color) values (?, ?, ?, ?)", [$canvas, $edit_x, $edit_y, $edit_color])->fetch();

        // ---> TODO: Update the user's last_edit_at time here! <---
        $db -> runQuery("update users set last_edit_at = current_timestamp where userID = ?", [$user->userID])->fetch();

        // Send a clean JSON success message back to JS
        $result = [
            "success" => true,
            "message" => "Pixel placed successfully!"
        ];
    }
    else {
        // They clicked too fast! (Maybe they tried to hack the JS timer)
        $result = [
            "success" => false,
            "error" => "Cooldown active. You must wait."
        ];
    }
}


// 5. send data
echo json_encode($result);
exit;