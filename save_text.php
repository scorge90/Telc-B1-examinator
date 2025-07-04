<?php
function save_text_to_db($conn, $student_text, $data, $template_text, $word_count, $error_counts) {
    $stmt = $conn->prepare("
        INSERT INTO b1_texts (
            student_text, corrected_text, comments,
            grammar_score, grammar_comment,
            vocabulary_score, vocabulary_comment,
            style_score, style_comment,
            content_score, content_comment,
            overall_score, overall_comment,
            template_letter, word_count, error_counts
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("❌ SQL prepare failed: " . $conn->error);
    }

    $correctedText  = $data["corrected_text"] ?? "";
    $commentsJson   = json_encode($data["comments"] ?? $data["corrections"] ?? [], JSON_UNESCAPED_UNICODE);

    $grammarScore      = (int) ($data["grades"]["Grammatik"]["note"] ?? 0);
    $grammarComment    = $data["grades"]["Grammatik"]["kommentar"] ?? "";

    $vocabularyScore   = (int) ($data["grades"]["Wortschatz"]["note"] ?? 0);
    $vocabularyComment = $data["grades"]["Wortschatz"]["kommentar"] ?? "";

    $styleScore        = (int) ($data["grades"]["Stil"]["note"] ?? 0);
    $styleComment      = $data["grades"]["Stil"]["kommentar"] ?? "";

    $contentScore      = (int) ($data["grades"]["Inhalt"]["note"] ?? 0);
    $contentComment    = $data["grades"]["Inhalt"]["kommentar"] ?? "";

    $overallScore      = (int) ($data["grades"]["Gesamtnote"]["note"] ?? 0);
    $overallComment    = $data["grades"]["Gesamtnote"]["kommentar"] ?? "";

  

   $stmt->bind_param(
    "sssisisisisissii",
    $student_text,
    $correctedText,
    $commentsJson,
    $grammarScore,
    $grammarComment,
    $vocabularyScore,
    $vocabularyComment,
    $styleScore,
    $styleComment,
    $contentScore,
    $contentComment,
    $overallScore,
    $overallComment,
    $template_text,
    $word_count,
    $error_counts
);




    if (!$stmt->execute()) {
        die("❌ SQL execute failed: " . $stmt->error);
    }

    $stmt->close();
    return true;
}
