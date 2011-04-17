<?php

require_once "signature.php";
require_once "highscore_storage.php";

$storage = new HighScoreStorage();

echo json_encode($storage->getHighScores());