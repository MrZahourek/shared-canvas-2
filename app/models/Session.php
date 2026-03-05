<?php
// app/models/Session.php

class Session {

    // 1. Generate raw random string for the cookie
    public static function generateRawToken($length = 32) {
        return bin2hex(random_bytes($length)); // Cryptographically secure!
    }

    // 2. Create a BRAND NEW session (Used when logging in with a password)
    public static function create($userID, $db) {
        $accessToken = self::generateRawToken();
        $refreshToken = self::generateRawToken();

        $accessHash = hash('sha256', $accessToken);
        $refreshHash = hash('sha256', $refreshToken);

        // Let MySQL handle the expiration math perfectly!
        $db->runQuery(
            "INSERT INTO active_sessions (userID, access_token_hash, access_token_expire, refresh_token_hash, refresh_token_expire, last_use) 
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), ?, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW())",
            [$userID, $accessHash, $refreshHash]
        );

        return ['access' => $accessToken, 'refresh' => $refreshToken];
    }

    // 3. Refresh an EXISTING session (Refresh Token Rotation)
    public static function refresh($oldRefreshToken, $db) {
        $oldHash = hash('sha256', $oldRefreshToken);

        $session = $db->runQuery(
            "SELECT * FROM active_sessions WHERE refresh_token_hash = ? AND refresh_token_expire > NOW()",
            [$oldHash]
        )->fetch();

        if (!$session) return false;

        $newAccess = self::generateRawToken();
        $newRefresh = self::generateRawToken();
        $newAccessHash = hash('sha256', $newAccess);
        $newRefreshHash = hash('sha256', $newRefresh);

        // Let MySQL update the expiration math perfectly!
        $db->runQuery(
            "UPDATE active_sessions 
             SET access_token_hash = ?, access_token_expire = DATE_ADD(NOW(), INTERVAL 15 MINUTE), refresh_token_hash = ?, refresh_token_expire = DATE_ADD(NOW(), INTERVAL 7 DAY), last_use = NOW() 
             WHERE sessionID = ?",
            [$newAccessHash, $newRefreshHash, $session['sessionID']]
        );

        return ['access' => $newAccess, 'refresh' => $newRefresh];
    }

    // 4. Garbage Collection (Deletes dead sessions)
    public static function cleanupExpired($db) {
        $db->runQuery("DELETE FROM active_sessions WHERE refresh_token_expire < NOW()");
    }

    // 5. Verify Access Token (Used on every single canvas click)
    public static function verifyAccessToken($rawAccessToken, $db) {
        if (!$rawAccessToken) return false;

        $hash = hash('sha256', $rawAccessToken);

        // Find the session and make sure the access token hasn't expired
        $session = $db->runQuery(
            "SELECT userID FROM active_sessions WHERE access_token_hash = ? AND access_token_expire > NOW()",
            [$hash]
        )->fetch();

        // If it exists and is valid, return the userID! Otherwise, return false.
        return $session ? $session['userID'] : false;
    }

    // 6. Logout (Destroys the session in the database)
    public static function logout($rawRefreshToken, $db) {
        if (!$rawRefreshToken) return;

        $hash = hash('sha256', $rawRefreshToken);

        // Nuke the session from the database so it can never be used again
        $db->runQuery("DELETE FROM active_sessions WHERE refresh_token_hash = ?", [$hash]);
    }
}
