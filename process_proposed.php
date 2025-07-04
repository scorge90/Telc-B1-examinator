<?php
session_start();
require_once 'db.php'; // includes db_connect()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Ungültige Anfrage.');
}

// Get mysqli connection
$conn = db_connect();

// Sanitize and retrieve POST data
$student_text       = trim($_POST['student_text'] ?? '');
$original_letter_key= trim($_POST['original_letter_key'] ?? '');
$proposed_grades    = $_POST['proposed_grades'] ?? [];
$proposed_comments  = $_POST['proposed_comments'] ?? [];
$proposed_result    = $_POST['proposed_result'] ?? '';

$errors = [];

// Validation
if (empty($student_text)) {
    $errors[] = 'Kein Text zur Bewertung übermittelt.';
}

$grade_keys = ['stil', 'wortschatz', 'grammatik', 'inhalt', 'gesamtnote'];
foreach ($grade_keys as $key) {
    if (!isset($proposed_grades[ucfirst($key)]) || !is_numeric($proposed_grades[ucfirst($key)])) {
        $errors[] = "Note für $key fehlt oder ist ungültig.";
    } else {
        $grade = (int)$proposed_grades[ucfirst($key)];
        if ($grade < 0 || $grade > 10) {
            $errors[] = "Note für $key muss zwischen 0 und 10 liegen.";
        }
    }
}

if (!in_array($proposed_result, ['BESTANDEN', 'NICHT BESTANDEN'])) {
    $errors[] = 'Ungültiges Ergebnis ausgewählt.';
}

if ($errors) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: proposed.php');
    exit;
}

// Map data
$stil_grade      = (int)$proposed_grades['Stil'];
$stil_comment    = $conn->real_escape_string(trim($proposed_comments['Stil'] ?? ''));

$wortschatz_grade = (int)$proposed_grades['Wortschatz'];
$wortschatz_comment = $conn->real_escape_string(trim($proposed_comments['Wortschatz'] ?? ''));

$grammatik_grade  = (int)$proposed_grades['Grammatik'];
$grammatik_comment= $conn->real_escape_string(trim($proposed_comments['Grammatik'] ?? ''));

$inhalt_grade     = (int)$proposed_grades['Inhalt'];
$inhalt_comment   = $conn->real_escape_string(trim($proposed_comments['Inhalt'] ?? ''));

$gesamtnote       = (int)$proposed_grades['Gesamtnote'];
$gesamtnote_comment = $conn->real_escape_string(trim($proposed_comments['Gesamtnote'] ?? ''));

$student_text_esc = $conn->real_escape_string($student_text);
$original_letter_key_esc = $conn->real_escape_string($original_letter_key);
$proposed_result_esc = $conn->real_escape_string($proposed_result);

$sql = "INSERT INTO proposed_notes (
            stil_grade, stil_comment,
            wortschatz_grade, wortschatz_comment,
            grammatik_grade, grammatik_comment,
            inhalt_grade, inhalt_comment,
            gesamtnote, gesamtnote_comment,
            result, created_at,
            student_text, original_letter_key
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Fehler bei der Vorbereitung der Abfrage: " . $conn->error;
    header('Location: proposed.php');
    exit;
}

$stmt->bind_param(
    "isissississss",
    $stil_grade, $stil_comment,
    $wortschatz_grade, $wortschatz_comment,
    $grammatik_grade, $grammatik_comment,
    $inhalt_grade, $inhalt_comment,
    $gesamtnote, $gesamtnote_comment,
    $proposed_result_esc,
    $student_text_esc,
    $original_letter_key_esc
);

$exec = $stmt->execute();

if ($exec) {
    $_SESSION['success'] = 'Bewertung erfolgreich gespeichert.';
} else {
    $_SESSION['error'] = 'Fehler beim Speichern: ' . $stmt->error;
}

$stmt->close();
$conn->close();

header('Location: index.php');
exit;
