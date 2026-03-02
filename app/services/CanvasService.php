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


/// 4. User Approved - Parse Data Safely
// Provide an empty array fallback in case the input is completely empty
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $data["action"] ?? "";
$result = ["success" => false];

// Safety check: Always default to "global" if the canvas name is missing or null
$canvasName = $data["canvasName"] ?? "global";
if (empty($canvasName) || $canvasName == "null") {
    $canvasName = "global";
}

if ($action == "init") {
    $canvasData = Canvas::getInit($canvasName, $db);
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
else if ($action == "get edits") {
    // Force this to be an integer! If JS sends the literal string "null", it will crash the DB!
    $last_edit_id = (int)($data["last_edit_id"] ?? 0);

    $query = $db->runQuery("SELECT editID, x, y, color FROM edit_history WHERE editID > ? AND canvas_name = ? ORDER BY editID ASC", [$last_edit_id, $canvasName])->fetchAll(PDO::FETCH_ASSOC);

    $result = [
        "success" => true,
        "edits" => $query ?: [] // Always return an array, even if empty
    ];
}
else if ($action == "new edit") {
    $edit_x = $data["x"];
    $edit_y = $data["y"];
    $edit_color = $data["color"];

    $now = time();
    $lastEditTime = $user->last_edit_at ? strtotime($user->last_edit_at) : 0;
    $diffSeconds = $now - $lastEditTime;
    $waitSeconds = intval($data["wait"] / 1000);

    if ($diffSeconds >= $waitSeconds) {
        $db->runQuery("INSERT INTO edit_history (canvas_name, x, y, color) VALUES (?, ?, ?, ?)", [$canvasName, $edit_x, $edit_y, $edit_color]);
        $db->runQuery("UPDATE users SET last_edit_at = current_timestamp WHERE userID = ?", [$user->userID]);

        $result = [
            "success" => true,
            "message" => "Pixel placed successfully!"
        ];
    } else {
        $result = ["success" => false, "error" => "Cooldown active."];
    }
}

// 5. Send data
echo json_encode($result);
exit;