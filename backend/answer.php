<?php

require_once "signature.php";
require_once "highscore_storage.php";

$questions = include('question_store.php');

$question = $_POST['question'];
$answer = $_POST['answer'];

if (! isset($questions[$question])) {
    die ('invalid question');
}

if ($questions[$question]['answer_correct'] === $answer) {
    $storage = new HighScoreStorage();
    $storage->increment($_GET['opensocial_viewer_id']);
    echo 1;
    exit;
}

echo 0;