<?php

class Canvas {

    public ?string $canvas_name;
    public ?string $config;
    public ?string $snap;

    public function __constructor($canvas_name) {
        $this->canvas_name = $canvas_name;
    }

    public function getConfig($db) {
        $this->config = $db->runQuery("select config from canvas_configs where canvas_name = ?", [$this->canvas_name]);
        return $this->config;
    }
    public function getSnapshot($db) {
        $this->snap =  $db->runQuery("SELECT last_edit_id, snapshot FROM canvas_snapshots WHERE canvas_name = ? order by snapshotID desc limit 1", [$this->canvas_name])->fetch();
        return $this->snap;
    }

    public function getEdits($db, $lastID) {
        $edits = [];
        $sql = "select x, y, color from edit_history where canvas_name = ? and editID < ? order by editID asc limit 1";
    }

    public function newEdit($db, $color, $x, $y) {}

    public function newSnapshot($db) {}


}