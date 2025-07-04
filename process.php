<?php
// Start session to store temporary data between page requests (e.g., error or result messages)
session_start();

// Set maximum execution time for the script to 120 seconds (in case AI processing is slow)
ini_set('max_execution_time', 120);
set_time_limit(120);

// Include database connection function
include 'db.php';

// Include a function to save results to the database
include 'save_text.php';

// Include navigation or footer bar (optional UI component)
include 'bar.php';

// -------- Load template letters from /brief_aufgaben folder -------- //
$sample_letters = [];  // Initialize empty array
$folder_path = __DIR__ . '/brief_aufgaben/';  // Path to the letter folder

// Loop through all .txt files and add their contents to the array
foreach (glob($folder_path . '*.txt') as $file_path) {
    $key = basename($file_path, '.txt');  // Extract filename without extension
    $content = file_get_contents($file_path);  // Read the content of the file
    if ($content) {
        $sample_letters[$key] = trim($content);  // Save cleaned content
    }
}

// -------- Only continue if the form was submitted and contains student_text -------- //
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty(trim($_POST["student_text"]))) {
    $student_text   = trim($_POST["student_text"]);  // Get the user's input
    $template_text  = '';  // Placeholder for the selected template letter text
    $template_key   = '';  // Placeholder for the key (filename)

    // Determine the source of the template: either via key or full letter string
    if (!empty($_POST['original_letter_key']) && isset($sample_letters[$_POST['original_letter_key']])) {
        $template_key  = $_POST['original_letter_key'];
        $template_text = $sample_letters[$template_key];
    } elseif (!empty($_POST['original_letter'])) {
        $template_text = trim($_POST['original_letter']);
    }

    // Prepare the data that will be passed to Python
    $input_data = [
        'student_text'    => $student_text,
        'original_letter' => $template_text,
    ];

    // -------- Create temporary directory if it doesn't exist -------- //
    $tmp_dir = __DIR__ . '/tmp';
    if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0777, true);

    // Save the input data to a temporary JSON file
    $temp_file = $tmp_dir . '/temp_input_' . uniqid() . '.json';
    file_put_contents($temp_file, json_encode($input_data, JSON_UNESCAPED_UNICODE));

    // Save a human-readable version of the input (for debugging)
    file_put_contents($tmp_dir . '/last_debug.json', json_encode($input_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // -------- Execute the Python script with the input file -------- //
    $python  = 'C:\\Users\\David Jovanovic\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';  // Path to Python interpreter
    $script  = 'C:\\xampp\\htdocs\\B1 Bewertung\\evaluate.py';  // Path to Python script
    $command = "\"$python\" \"$script\" " . escapeshellarg($temp_file);  // Full command with escaping
    $output  = shell_exec($command);  // Run the command and capture the output

    // Save debug log with executed command and script output
    file_put_contents($tmp_dir . '/python_debug.txt', "Command:\n$command\n\nOutput:\n$output");

    // Delete the temporary input file
    unlink($temp_file);

    // -------- Check if the Python script returned valid output -------- //
    if ($output === false || trim($output) === '') {
        $_SESSION['error'] = "❌ Keine Antwort vom Python-Skript erhalten.";
        header("Location: index.php");
        exit();
    }

    // Try to decode the JSON result from Python
    $data = json_decode($output, true);
    if ($data === null) {
        $_SESSION['error'] = "JSON decoding failed: " . json_last_error_msg();
        header("Location: index.php");
        exit();
    }

    // -------- Count words in the student text -------- //
    function count_words($text) {
        return str_word_count(trim($text), 0, "äöüÄÖÜß");  // Add German characters
    }
    $word_count = count_words($student_text);

    // -------- Count total errors from error_counts array -------- //
    $error_counts = 0;
    if (!empty($data["error_counts"]) && is_array($data["error_counts"])) {
        $error_counts = array_sum($data["error_counts"]);
    }

    // -------- Connect to the database -------- //
    $conn = db_connect();
    if (!$conn) {
        $_SESSION['error'] = "❌ Datenbankverbindung fehlgeschlagen.";
        header("Location: index.php");
        exit();
    }

    // -------- Save data to the database -------- //
    $success = save_text_to_db($conn, $student_text, $data, $template_text, $word_count, $error_counts);
    if (!$success) {
        $_SESSION['error'] = "❌ Fehler beim Speichern in die Datenbank.";
        header("Location: index.php");
        exit();
    }

    // -------- Save result to session for display on index.php -------- //
    $_SESSION['result'] = [
        'student_text'    => $student_text,
        'template_letter' => $template_text,
        'data'            => $data,
        'template_key'    => $template_key
    ];

    // -------- Optional: separate visible and invisible parts of the template -------- //
    $parts = explode('---INVISIBLE---', $_POST['original_letter'] ?? '');
    $visible_letter = trim($parts[0]);  // Shown to student
    $invisible_instructions = trim($parts[1] ?? '');  // Instructions for AI

    // -------- Save both visible and invisible parts for future analysis -------- //
    $letter_data = [
        "template_letter" => $visible_letter,
        "instructions"    => $invisible_instructions
    ];
    file_put_contents("input.json", json_encode($letter_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // -------- Redirect back to index.php to display the result -------- //
    header("Location: index.php");
    exit();
}


