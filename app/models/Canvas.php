<?php

class Canvas {

    public static function findByName($name, $db) {
        return $db->runQuery(
            "SELECT * FROM canvas_configs WHERE canvas_name = ?",
            [$name]
        )->fetch();
    }

    public static function getSnapshot($name, $db) {
        return $db->runQuery(
            "SELECT snapshot FROM canvas_snapshots ORDER BY snapshotID DESC WHERE canvas_name = ? LIMIT 1",
            [$name]
        )->fetch();
    }

    public static function getEdits($lastID, $name, $db) {
        return $db->runQuery(
            "SELECT * FROM edit_history ORDER BY editID ASC WHERE editID => ? AND canvas_name = ?",
            [$lastID, $name]
        )->fetch();
    }
}