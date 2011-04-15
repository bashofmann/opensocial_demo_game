<?php

require_once "signature.php";

$questions = include('question_store.php');

header('Content-Type: application/json');
echo json_encode($questions[$_GET['question']]['question']);