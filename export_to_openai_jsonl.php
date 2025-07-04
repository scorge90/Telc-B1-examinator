<?php
// export_to_openai_chat_jsonl.php

$host     = 'localhost';
$user     = 'root';
$password = '';
$database = 'text_b1_bewertung';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$sql = "
  SELECT
    student_text,
    corrected_text,
    comments,
    grammar_score, grammar_comment,
    vocabulary_score, vocabulary_comment,
    style_score, style_comment,
    content_score, content_comment,
    overall_score, overall_comment
  FROM b1_texts
";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$out = fopen(__DIR__ . "/openai_chat_export.jsonl", "w");
if (!$out) {
    die("Could not open output file for writing.");
}

while ($row = $result->fetch_assoc()) {
    // decode corrections array
    $corrections = json_decode($row['comments'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $corrections = [];
    }

    // build assistant content
    $assistant = "Korrigierter Text:\n" . $row['corrected_text'] . "\n\n";
    if (count($corrections)) {
        $assistant .= "Kommentare:\n";
        foreach ($corrections as $c) {
            $assistant .= "- '{$c['original']}' → '{$c['corrected']}'\n";
            $assistant .= "  Kommentar: {$c['comment']}\n";
        }
        $assistant .= "\n";
    }
    $assistant .= "📊 Grammatik: {$row['grammar_score']} — {$row['grammar_comment']}\n";
    $assistant .= "📊 Wortschatz: {$row['vocabulary_score']} — {$row['vocabulary_comment']}\n";
    $assistant .= "📊 Stil: {$row['style_score']} — {$row['style_comment']}\n";
    $assistant .= "📊 Inhalt: {$row['content_score']} — {$row['content_comment']}\n\n";
    $assistant .= "🏆 Gesamtnote: {$row['overall_score']} — {$row['overall_comment']}";

    $example = [
        "messages" => [
            [
                "role"    => "system",
                "content" => "You are a German language teacher. Correct student writing, explain mistakes, and give a final grade."
            ],
            [
                "role"    => "user",
                "content" => $row['student_text']
            ],
            [
                "role"    => "assistant",
                "content" => $assistant
            ]
        ]
    ];

    fwrite($out, json_encode($example, JSON_UNESCAPED_UNICODE) . "\n");
}

fclose($out);
$conn->close();

echo "✅ Chat-style export complete: openai_chat_export.jsonl\n";
