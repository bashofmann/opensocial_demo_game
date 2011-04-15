<?php
$handle = fopen("questions.csv", "r");
$questions = array();
$i = 0;
while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE ) {
    $i++;
    if ($i === 1) {
        continue;
    }
    $answers = array($data[1], $data[2], $data[3], $data[4]);
    shuffle($answers);
    $question = array(
        'id' => $i - 1,
        'question' => $data[0],
        'answer_correct' => $data[1],
        'answers' => $answers,
    );
    $questions[$i - 1] = $question;
}
fclose ($handle);

return $questions;