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
        // -> user login data correct ... try to look for refresh cooke

        $refresh_token = $_COOKIE["refresh_token"] ?? null;
        if (empty($refresh_token)) {
            // -> no token found ... make new session
            $tokens = Session::create($user["userID"], $db);

            // ... Do a little database cleanup while we are here
            Session::cleanupExpired($db);

            // ... Send the tokens to the browser cookies
            setcookie("access_token", $tokens['access'], time() + (15 * 60), "/", "", false, true); // HttpOnly cookie
            setcookie("refresh_token", $tokens['refresh'], time() + (7 * 24 * 60 * 60), "/", "", false, true);
        }
        else {
            // -> token found ... refresh old session
            // clean old sessions
            Session::cleanupExpired($db);

            // refresh
            $tokens = Session::refresh($refresh_token, $db);

            if (!empty($tokens["refresh"]) && !empty($tokens["access"])) {
                // ... Send the tokens to the browser cookies
                setcookie("access_token", $tokens['access'], time() + (15 * 60), "/", "", false, true); // HttpOnly cookie
                setcookie("refresh_token", $tokens['refresh'], time() + (7 * 24 * 60 * 60), "/", "", false, true);
            }
            else {
                // -> invalid token
                $response['errorList'][] = "Invalid session token";
            }
        }

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
        $tokens = Session::create($newUser["userID"], $db);

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