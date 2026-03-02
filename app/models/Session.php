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

        // We hash them for the DB so if the DB is stolen, the hackers don't get the active tokens
        $accessHash = hash('sha256', $accessToken);
        $refreshHash = hash('sha256', $refreshToken);

        // Expiration times: Access = 15 mins, Refresh = 7 days
        $accessExpire = date('Y-m-d H:i:s', time() + (15 * 60));
        $refreshExpire = date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60));

        $db->runQuery(
            "INSERT INTO active_sessions (userID, access_token_hash, access_token_expire, refresh_token_hash, refresh_token_expire, last_use) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$userID, $accessHash, $accessExpire, $refreshHash, $refreshExpire]
        );

        // Return the RAW tokens so AuthService can put them in the user's cookies
        return [
            'access' => $accessToken,
            'refresh' => $refreshToken
        ];
    }

    // 3. Refresh an EXISTING session (Refresh Token Rotation)
    public static function refresh($oldRefreshToken, $db) {
        $oldHash = hash('sha256', $oldRefreshToken);

        // Find the session. Make sure the refresh token hasn't expired yet!
        $session = $db->runQuery(
            "SELECT * FROM active_sessions WHERE refresh_token_hash = ? AND refresh_token_expire > NOW()",
            [$oldHash]
        )->fetch();

        if (!$session) {
            return false; // Token is invalid, fake, or expired. Kick them out!
        }

        // Generate brand new tokens for the next cycle
        $newAccess = self::generateRawToken();
        $newRefresh = self::generateRawToken();

        $newAccessHash = hash('sha256', $newAccess);
        $newRefreshHash = hash('sha256', $newRefresh);

        $accessExpire = date('Y-m-d H:i:s', time() + (15 * 60));
        $refreshExpire = date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60));

        // Update the EXACT SAME row with the new tokens (Rotation)
        $db->runQuery(
            "UPDATE active_sessions 
             SET access_token_hash = ?, access_token_expire = ?, refresh_token_hash = ?, refresh_token_expire = ?, last_use = NOW() 
             WHERE sessionID = ?",
            [$newAccessHash, $accessExpire, $newRefreshHash, $refreshExpire, $session['sessionID']]
        );

        return [
            'access' => $newAccess,
            'refresh' => $newRefresh
        ];
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
