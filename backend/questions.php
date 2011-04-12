<?php

require_once "signature.php";

$questions = include('question_store.php');

$i = rand(0, count($questions) - 1);

unset($questions[$i]['answer_correct']);
header('Content-Type: application/json');
echo json_encode($questions[$i]);