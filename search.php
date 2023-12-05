<?php

$query = "php";
$n = 100;

$output_dir = "fetched/";

$domObjects = [];

$htmlFiles = glob($output_dir . '/*.html');
foreach ($htmlFiles as $htmlFile) {
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTMLFile($htmlFile);
    libxml_clear_errors();
    $domObjects[] = $dom;
}

$domData = [];

foreach ($domObjects as $dom) {
    $elementTypes = ['title', 'h1', 'p', 'span', 'a', 'li', 'td', 'label', 'button'];

    foreach ($elementTypes as $elementType) {
        $elements = $dom->getElementsByTagName($elementType);

        $elementTexts = [];
        foreach ($elements as $element) {
            $elementTexts[] = $element->textContent;
        }

        $domData = array_merge($domData, $elementTexts);
    }
}

$domData = array_filter(array_map('trim', $domData), function($value) {
    return $value !== '';
});

$queryVector = array_count_values(str_split($query));

$documentVectors = [];

foreach ($domData as $document) {
    $documentVector = array_count_values(str_split($document));
    $documentVectors[$document] = $documentVector;
}

$scores = [];

foreach ($documentVectors as $document => $documentVector) {
    $dotProduct = 0;
    $magnitudeA = 0;
    $magnitudeB = 0;

    foreach ($queryVector as $key => $value) {
        if (isset($documentVector[$key])) {
            $dotProduct += $value * $documentVector[$key];
        }

        $magnitudeA += pow($value, 2);
    }

    foreach ($documentVector as $value) {
        $magnitudeB += pow($value, 2);
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    $score = ($magnitudeA == 0 || $magnitudeB == 0) ? 0 : $dotProduct / ($magnitudeA * $magnitudeB);
    $scores[$document] = $score;
}

arsort($scores);

$topN = array_slice($scores, 0, $n, true);

$answer = implode('. ', array_keys($topN));

$lowercaseAnswer = strtolower($answer);
$lowercaseQuery = strtolower($query);

$highlightedAnswer = preg_replace_callback(
    "/$lowercaseQuery/",
    function ($match) use ($query) {
        return '<u><i>' . substr($match[0], 0, strlen($query)) . '</i></u>' . substr($match[0], strlen($query));
    },
    $answer
);

print_r($highlightedAnswer);

?>
