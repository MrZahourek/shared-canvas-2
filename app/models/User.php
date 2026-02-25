<?php

include "../app/controllers/conn.php";

// Initialize Database
$db = new Database();

// Get the JSON data sent from userEntry.js
$data = json_decode(file_get_contents("php://input"), true);

// Its assumed we already did the token check so...
$sql = "select userID from active_sessions where access_token_hash = ?";
$stmt = $db->runQuery($sql, [password_hash($_COOKIE['access token'], PASSWORD_DEFAULT)]);
$user = $stmt->fetch();

foreach ($data["requests"] as $key) {
    $sql = "select ? from users where userID = ?";
    $stmt = $db->runQuery($sql, [$key, $user['userID']]);
    $res = $stmt->fetch();
    $user[$key] = $res[$key];
}

echo json_encode($user);