<?php
function display_output($data) {

    // Display corrected text
    if (!empty($data["corrected_text"])) {
        $safe_text = htmlspecialchars($data["corrected_text"], ENT_QUOTES | ENT_SUBSTITUTE);
        $safe_text_with_breaks = nl2br($safe_text);
        echo "<h2>📄 Gesamter korrigierter Text:</h2>";
        echo "<div style='white-space: pre-wrap; border:1px solid #4CAF50; padding:10px; background:#e8f5e9;'>";
        echo $safe_text_with_breaks;
        echo "</div>";
    } else {
        echo "<h2>ℹ️ Kein korrigierter Text verfügbar.</h2>";
    }

    // Display comments if available
    if (!empty($data["comments"]) && is_array($data["comments"])) {
        echo "<h2>💬 Kommentare:</h2>";
        echo "<div class='corrected-box' style='border:1px solid #2196F3; padding:10px; background:#e3f2fd;'>";
        foreach ($data["comments"] as $entry) {
            echo "<div style='margin-bottom:15px;'>";
            echo "<strong>🔴 Original:</strong><br>" . nl2br(htmlspecialchars($entry['original'])) . "<br>";
            echo "<strong>🟢 Korrektur:</strong><br>" . nl2br(htmlspecialchars($entry['corrected'])) . "<br>";
            echo "<strong>🔹 Kommentar:</strong><br>" . nl2br(htmlspecialchars($entry['comment'])) . "<br>";
            echo "</div><hr style='border-color:#bbb;'>";
        }
        echo "</div>";
    }

    // Display grades summary
    $sections = [
        "Inhalt"     => "📊 Inhalt",
        "Stil"       => "📊 Stil",
        "Wortschatz" => "📊 Wortschatz",
        "Grammatik"  => "📊 Grammatik",
        "Gesamtnote" => "📊 Gesamtnote"
    ];

    if (!empty($data["grades"]) && is_array($data["grades"])) {
        echo "<h2>📝 Bewertungen:</h2>";
        echo "<div class='grade-box' style='border:1px solid #ff9800; padding:10px; background:#fff3e0;'>";
        foreach ($sections as $key => $label) {
            if (isset($data["grades"][$key])) {
                if (is_array($data["grades"][$key])) {
                    $note      = htmlspecialchars($data["grades"][$key]["note"] ?? '');
                    $kommentar = htmlspecialchars($data["grades"][$key]["kommentar"] ?? '');
                    echo "<h3 style='margin-bottom:5px;'>$label:</h3>";
                    echo "<p><strong>Note:</strong> $note<br><em>$kommentar</em></p>";
                } else {
                    $note = htmlspecialchars($data["grades"][$key]);
                    echo "<h3 style='margin-bottom:5px;'>$label:</h3>";
                    echo "<p><strong>Note:</strong> $note</p>";
                }
            }
        }

        if (!empty($data["grades"]["Gesamtnote"]["ergebnis"])) {
            $ergebnis = htmlspecialchars($data["grades"]["Gesamtnote"]["ergebnis"]);
            echo "<h3>🎯 Ergebnis:</h3>";
            echo "<p style='font-weight:bold; font-size:1.2em;'>$ergebnis</p>";
        }
        echo "</div>";
    }

    // Display error counts (spelling etc.)
    if (!empty($data["error_counts"]) && is_array($data["error_counts"])) {
        echo "<h2>📉 Fehlerstatistik:</h2>";
        echo "<ul style='border:1px solid #f44336; padding:10px; background:#ffebee;'>";
        foreach ($data["error_counts"] as $type => $count) {
            echo "<li><strong>" . ucfirst(htmlspecialchars($type)) . ":</strong> " . htmlspecialchars($count) . "</li>";
        }
        echo "</ul>";
    }

    // SELBST BEWERTEN button with hidden inputs from session
    $student_text = $_SESSION['result']['student_text'] ?? '';
    $original_letter = $_SESSION['result']['original_letter'] ?? '';
    $original_letter_key = $_SESSION['result']['original_letter_key'] ?? '';

    echo '<form method="POST" action="proposed.php" style="margin-top:20px;">';
    echo '<input type="hidden" name="student_text" value="' . htmlspecialchars($student_text) . '">';
    echo '<input type="hidden" name="original_letter" value="' . htmlspecialchars($original_letter) . '">';
    echo '<input type="hidden" name="original_letter_key" value="' . htmlspecialchars($original_letter_key) . '">';
    echo '<button type="submit" style="padding:10px 20px; font-size:16px; background:#4CAF50; color:#fff; border:none; cursor:pointer;">SELBST BEWERTEN</button>';
    echo '</form>';
}
