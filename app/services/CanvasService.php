<?php
// get the input data
$data = json_decode(file_get_contents("php://input"), true);

switch ($data["action"]) {
    case "new edit":
        break;

    case "get edits":
        break;

    case "get snapshot":
        break;
}