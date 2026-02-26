<?php
include "../app/controllers/conn.php";

class User {
    public static function findById($id, $db) {
        return $db->runQuery(
            "SELECT * FROM users WHERE userID = ?",
            [$id]
        )->fetch();
    }

    public static function checkName($username, $db){
        $result = json_decode('{ "success": false, "error": [] }');

        $query =  $db->runQuery(
            "select username from users where username = ?",
            [$username]
        )->fetch();

        if (count($query) > 0) {
            $result->success = true;
            $result->error = [];
            return $result;
        }
        else {
            $result->success = false;
            $result->error[] = "username taken";
            return $result;
        }
    }

    public static function checkLogin($username, $password, $db){
        $result = json_decode('{ "success": false, "error": [] }');

        $query = $db->runQuery(
            "select username, password_hash from users where username = ?",
            [$username]
        )->fetch();

        if (count($query) <= 0) {
            $result->success = false;
            $result->error[] = "user doesnt exist";
            return $result;
        }

        if (password_verify($password, $query["password_hash"])) {
            $result->success = true;
            $result->error = [];
            return $result;
        }
        else {
            $result->success = false;
            $result->error[] = "incorrect password";
            return $result;
        }

    }

    public static function createUser($username, $password, $db) {}
}

$data = json_decode(file_get_contents("php://input"), true);
$User = new User();
$db = new Database();

switch ($data["request"]) {
    case "check login":
        echo json_encode($User->checkLogin($data["username"], $data["password"], $db));
        break;

    case "check name":
        echo json_encode($User->checkName($data["username"], $db));
        break;
}