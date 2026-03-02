<?php
// app/models/User.php

class User {
    // Adding the '?' makes them "nullable", meaning they are allowed to be empty/null!
    // Also changed last_edit_at to string since MySQL timestamps look like "2026-02-25 14:30:00"
    public ?int $userID;
    public ?string $username;
    public ?string $password_hash;
    public ?string $last_edit_at;

    // The constructor sets up the object when you use 'new User()'
    public function __construct($dbRow) {
        $this->userID = $dbRow['userID'] ?? null;
        $this->username = $dbRow['username'] ?? null;
        $this->password_hash = $dbRow['password_hash'] ?? null;
        $this->last_edit_at = $dbRow['last_edit_at'] ?? null;
    }

    // --- INSTANCE METHODS (Things this specific user can do) ---

    // Clean password checking!
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }


    // --- STATIC METHODS (Tools to find users in the database) ---

    public static function findById($id, $db) {
        $row = $db->runQuery("SELECT * FROM users WHERE userID = ?", [$id])->fetch();
        if ($row) {
            return new User($row); // Returns a real User object!
        }
        return null;
    }

    public static function findByUsername($username, $db) {
        $row = $db->runQuery("SELECT * FROM users WHERE username = ?", [$username])->fetch();
        if ($row) {
            return new User($row); // Returns a real User object!
        }
        return null;
    }

    // Creates the user in the DB and returns the new User object
    public static function create($username, $password, $db) {
        // Hash the password securely
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $db->runQuery(
            "INSERT INTO users (username, password_hash) VALUES (?, ?)",
            [$username, $hash]
        );

        // Fetch and return the newly created user
        return self::findByUsername($username, $db);
    }

    public function getInit() {
        return [
            "username" => $this->username,
            "last_edit_at" => $this->last_edit_at ? strtotime($this->last_edit_at) * 1000 : null
        ];
    }
}