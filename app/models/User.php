<?php
class User {
    public static function findById($id, $db) {
        return $db->runQuery(
            "SELECT * FROM users WHERE userID = ?",
            [$id]
        )->fetch();
    }
}