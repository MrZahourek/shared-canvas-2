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

// Initialize Database
$db = new Database();

// Get the JSON data sent from userEntry.js
$data = json_decode(file_get_contents("php://input"), true);

// Prepare response
$response = ["success" => false, "errorList" => []];

// # Login of user
if ($data["authType"] == "login") {
    // check if the input is correct
    $username = $data["username"];
    $password = $data["password"];

    // 1. Check if the user exists
    $sql = "SELECT userID, username, password_hash FROM users WHERE username = ?";
    $stmt = $db->runQuery($sql, [$username]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Verify the password against the stored hash
        if (password_verify($password, $user['password_hash'])) {
            // SUCCESS
            $response["success"] = true;

            // Token check
            if (isset($_COOKIE["refresh token"])) {
                // if the refresh token is still set in cookie get the hash by the logged user id (if possible)
                $token = $_COOKIE["refresh token"];
                $sql = "SELECT refresh_token_hash FROM active_sessions where userID = ?";
                $stmt = $db->runQuery($sql, [$user["userID"]]);
                $token_hash = $stmt->fetch();

                // check
                if($token_hash) {
                    // does the set token match the user
                    if (password_verify($token, $token_hash)) {
                        // user is connected again
                        generateToken("access", $db);
                        refreshToken("refresh", $db);
                    }
                    else {
                        // different user
                    }
                }
            }
        } else {
            // Wrong password
            $response["errorList"][] = "Invalid password.";
        }
    } else {
        // User not found
        $response["errorList"][] = "Invalid username - user doesnt exist.";
    }

    // Return the result to your JavaScript
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// # New user
if ($data["authType"] == "newUser") {}

// # User request
if ($data["authType"] == "authenticate") {}