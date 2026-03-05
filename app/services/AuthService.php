<?php
// app/services/AuthService.php
require_once dirname(__DIR__) . "/controllers/conn.php";
require_once dirname(__DIR__) .  "/models/User.php";
require_once dirname(__DIR__) . "/models/Session.php";


// Initialize Database
$db = new Database();

// Get the JSON data sent from fetch
$data = json_decode(file_get_contents("php://input"), true);

$response = ["success" => false, "errorList" => []];

// --- LOGIN FLOW ---
if ($data["authType"] == "login") {

    // 1. Find the user object
    $user = User::findByUsername($data["username"], $db);

    // 2. Check if they exist AND if the password matches using our new method
    if ($user && $user->verifyPassword($data["password"])) {

        // -> User login data correct!
        // THE FIX: We DO NOT care about old cookies. If they proved their password,
        // we ALWAYS generate a brand new session and forcefully overwrite the browser cookies!

        $tokens = Session::create($user->userID, $db);

        // ... Do a little database cleanup while we are here
        Session::cleanupExpired($db);

        // ... Send the BRAND NEW tokens to the browser cookies (This instantly overwrites the old ones!)
        setcookie("access_token", $tokens['access'], time() + (15 * 60), "/", "", false, true);
        setcookie("refresh_token", $tokens['refresh'], time() + (7 * 24 * 60 * 60), "/", "", false, true);

        $response["success"] = true;

    } else {
        // -> user login data wrong ... die and send errors
        if (empty($user)) {
            $response["errorList"][] = "User not found";
        }
        elseif (!$user->verifyPassword($data["password"])) {
            $response["errorList"][] = "Incorrect password";
        }
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

        // -> new user ... make new session
        $tokens = Session::create($newUser->userID, $db);

        // ... Do a little database cleanup while we are here
        Session::cleanupExpired($db);

        // ... Send the tokens to the browser cookies
        setcookie("access_token", $tokens['access'], time() + (15 * 60), "/", "", false, true); // HttpOnly cookie
        setcookie("refresh_token", $tokens['refresh'], time() + (7 * 24 * 60 * 60), "/", "", false, true);

        $response["success"] = true;
    }
}

// end of code
echo json_encode($response);
exit;