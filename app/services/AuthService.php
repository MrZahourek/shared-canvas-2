<?php
/* app/services/AuthService.php
** -> Here we send requests to get authentication of the user
 *
 * todo:
 * - new user maker
 * - check login
 * - token security
*/
include "../app/services/TokenService.php";

require_once "../app/controllers/conn.php";
require_once "../app/models/User.php";

// Initialize Database
$db = new Database();

// Get the JSON data sent from fetch
$data = json_decode(file_get_contents("php://input"), true);

$response = ["success" => false, "errorList" => []];

function getUserID ($db) {
    $result = (object) ['success' => false, 'userID' => null];

    // 1. get access
    $accessToken = getToken("access");

    // 2. if no access make new access
    if (!$accessToken["success"]) {
        generateToken("access", $db);
        $accessToken = getToken("access");
    }

    // 3. get userID with access
    $session = $db->runQuery(
        "select * from active_sessions where access_token_hash = ?",
        [hash('sha256', $accessToken["token"])]
    )->fetch();

    // 4. update last_use
    $db->runQuery("update active_sessions set last_use = ? where access_token_hash = ?", [time(), hash('sha256', $accessToken["token"])]);

    $result->success = true;
    $result->userID = $session["userID"];
    return $result;
}

// --- LOGIN FLOW ---
if ($data["authType"] == "login") {

    // 1. Find the user object
    $user = User::findByUsername($data["username"], $db);

    // 2. Check if they exist AND if the password matches using our new method
    if ($user && $user->verifyPassword($data["password"])) {

        $response["success"] = true;
        // Proceed to generate session tokens using $user->userID ...

    } else {
        $response["errorList"][] = "Invalid username or password.";
    }

// --- NEW USER FLOW ---
} else if ($data["authType"] == "newUser") {

    // 1. Check if name is taken
    $existingUser = User::findByUsername($data["username"], $db);

    if ($existingUser) {
        $response["errorList"][] = "Username is already taken.";
    } else {
        // 2. Create the user
        $newUser = User::create($data["username"], $data["password"], $db);

        $response["success"] = true;
        // Proceed to generate session tokens using $newUser->userID ...
    }
}

// end of code
echo json_encode($response);
exit;