<!-- index.php -->
<?php
// Start a session to use session variables
session_start();

// Include a file that contains a function for displaying output (e.g., corrections, grades)
include 'display_output.php';

// Prepare an array to hold all sample letters
$sample_letters = [];

// Define the folder path where sample letters are stored
$folder_path = __DIR__ . '/brief_aufgaben/';

// Loop through all `.txt` files in the folder
foreach (glob($folder_path . '*.txt') as $file_path) {
    // Use the filename (without .txt) as the key
    $key = basename($file_path, '.txt');
    
    // Read the content of the file
    $content = file_get_contents($file_path);
    
    // If the file is not empty, trim it and add it to the array
    if ($content) {
        $sample_letters[$key] = trim($content);
    }
}

// Try to get the selected letter key (either newly selected or already submitted)
$selected_letter_key = $_POST['selected_letter'] ?? ($_POST['original_letter_key'] ?? '');

// Fetch the letter text using the selected key
$selected_letter_text = $sample_letters[$selected_letter_key] ?? '';
?>

<!-- HTML part starts -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>B1 Textkorrektur</title>
    <!-- Load custom CSS styles -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php if (!empty($_SESSION['error'])): ?>
    <!-- If there's an error message in the session, show it -->
    <div class="message error">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); // Remove error after displaying ?>
    </div>
<?php endif; ?>

<div class="container">
    <h1>📝 B1 Textkorrektur</h1>

    <!-- Section: Select a template letter -->
    <section class="letter-box">
        <h2>📬 Wählen Sie einen Brief, auf den Sie antworten möchten:</h2>
        
        <!-- Form for selecting a letter from dropdown -->
        <form method="post">
            <select name="selected_letter" onchange="this.form.submit()">
                <option value="">-- Bitte wählen --</option>
                <?php foreach ($sample_letters as $key => $text): ?>
                    <!-- Populate the dropdown with letter titles -->
                    <option value="<?= htmlspecialchars($key) ?>" <?= ($selected_letter_key === $key) ? 'selected' : '' ?>>
                        <?= ucfirst(htmlspecialchars($key)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php
        // If a letter was selected, display the visible part
        if (!empty($selected_letter_text)) {
            // Separate visible and invisible parts using a custom marker
            $parts = explode('---INVISIBLE---', $selected_letter_text);
            $visible_text = trim($parts[0]); // Show this part to the user
            $invisible_text = $parts[1] ?? ''; // Hidden, used internally
            
            // Display the visible part of the letter
            echo "<h3>📄 Brief:</h3>";
            echo "<pre>" . htmlspecialchars($visible_text) . "</pre>";
        }
        ?>
    </section>

    <!-- Section: Text input field for user's answer -->
    <section class="letter-box">
        <h2>✍️ Schreiben Sie Ihre Antwort oder Ihren Brief:</h2>

        <!-- Form to submit student's text for correction -->
        <form method="POST" action="process.php">
    <textarea id="student_text" name="student_text" rows="10" placeholder="Antwort oder eigener Brief..." required><?= 
    isset($_SESSION['result']['student_text']) ? htmlspecialchars(trim($_SESSION['result']['student_text'])) : '' ?></textarea><br>
        </textarea><br>

            <?php if (!empty($selected_letter_text)): ?>
                <!-- Hidden fields to pass the original letter back to server -->
                <input type="hidden" name="original_letter" value="<?= htmlspecialchars($selected_letter_text) ?>">
                <input type="hidden" name="original_letter_key" value="<?= htmlspecialchars($selected_letter_key) ?>">
            <?php endif; ?>

            <!-- Button to send data for correction -->
            <button type="submit">🛠️ Brief korrigieren</button>
        </form>
    </section>

    <!-- Word counter display -->
    <div id="wordCountDisplay">🧮 Wörter: <span id="wordCount">0</span></div>

    <!-- Section: Show the correction results -->
    <?php
    if (isset($_SESSION['result'])) {
        echo "<div class='result'>";
        // Use display_output() function to show corrections/feedback
        display_output($_SESSION['result']['data']);
        echo "</div>";

        // Remove the result from session so it doesn't persist on refresh
        unset($_SESSION['result']);
    }
    ?>
</div>

<!-- Load helper JavaScript for scrolling and word counting -->
<script src="word_counter.js"></script>

<!-- Include footer/navigation bar -->
<?php include 'bar.php'; ?>

</body>
</html>
