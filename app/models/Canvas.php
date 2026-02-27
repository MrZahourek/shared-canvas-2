<?php
// app/models/Canvas.php
require_once dirname(__DIR__) . "/controllers/conn.php";

class Canvas {
    public ?string $canvas_name;
    public ?int $snapshotID;
    public ?string $config;

    public function __construct($dbRow) {
        $this->canvas_name = $dbRow['canvas_name'] ?? null;
        $this->snapshotID = $dbRow['snapshotID'] ?? null;
        $this->config = $dbRow['config'] ?? null;
    }

    // --- STATIC METHODS (Tools to find and manage canvases in the DB) ---

    // 1. Find a canvas by its unique name (e.g., "global")
    public static function findByName($name, $db) {
        $row = $db->runQuery("SELECT * FROM canvas_configs WHERE canvas_name = ?", [$name])->fetch();
        if ($row) {
            return new Canvas($row);
        }
        return null;
    }

    // 2. Create a brand new canvas using your JSON templates
    public static function create($name, $db) {
        // Read your default JSON templates
        $defaultConfig = file_get_contents(dirname(__DIR__, 2) . "/database/canvasConfigTemplate.json");
        $defaultSnapshot = file_get_contents(dirname(__DIR__, 2) . "/database/canvasSnapshotTemplate.json");

        // Insert a blank snapshot first (last_edit_id starts at 0)
        $db->runQuery(
            "INSERT INTO canvas_snapshots (canvas_name, last_edit_id, snapshot) VALUES (?, 0, ?)",
            [$name, $defaultSnapshot]
        );

        // Fetch the ID of the snapshot we just created to link it
        $snapRow = $db->runQuery("SELECT snapshotID FROM canvas_snapshots WHERE canvas_name = ? ORDER BY snapshotID DESC LIMIT 1", [$name])->fetch();
        $snapID = $snapRow['snapshotID'];

        // Create the main config entry
        $db->runQuery(
            "INSERT INTO canvas_configs (canvas_name, snapshotID, config) VALUES (?, ?, ?)",
            [$name, $snapID, $defaultConfig]
        );

        return self::findByName($name, $db);
    }

    // 3. Fetch the absolute latest snapshot array
    public static function getSnapshot($name, $db) {
        $row = $db->runQuery(
            "SELECT * FROM canvas_snapshots WHERE canvas_name = ? ORDER BY snapshotID DESC LIMIT 1",
            [$name]
        )->fetch();

        if ($row) {
            // Decode the JSON string into a PHP array so it can be cleanly sent to JS later
            $row['snapshot'] = json_decode($row['snapshot'], true);
        }
        return $row;
    }

    // 4. Fetch only the pixels placed AFTER a specific edit ID
    public static function getEdits($name, $lastID, $db) {
        // Ensure $lastID is a number (defaults to 0 if null)
        $lastID = $lastID ? (int)$lastID : 0;

        $stmt = $db->runQuery(
            "SELECT editID, x, y, color FROM edit_history WHERE canvas_name = ? AND editID > ? ORDER BY editID ASC",
            [$name, $lastID]
        );

        // Fetch all matching rows. If none match, return an empty array.
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // 5. THE MASTER INIT FUNCTION (Gathers everything for page load)
    public static function getInit($name, $db) {
        // Find the canvas. If they typed a custom name that doesn't exist, create it on the fly!
        $canvas = self::findByName($name, $db);
        if (!$canvas) {
            $canvas = self::create($name, $db);
        }

        // Gather the snapshot and its ID
        $snapshotData = self::getSnapshot($name, $db);
        $lastSnapshotEditID = $snapshotData ? $snapshotData['last_edit_id'] : 0;

        // Gather the loose pixels placed after that snapshot was taken
        $recentEdits = self::getEdits($name, $lastSnapshotEditID, $db);

        // Package everything together into one perfect array for the JavaScript
        return [
            "config" => json_decode($canvas->config, true),
            "snapshot" => $snapshotData ? $snapshotData['snapshot'] : [],
            "snapshot_last_id" => $lastSnapshotEditID,
            "recent_edits" => $recentEdits
        ];
    }
}