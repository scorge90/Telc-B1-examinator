<?php
session_start();

$student_text = $_POST['student_text'] ?? '';
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Proposed Notenformular</title>
  <style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
    background-color: #fafafa;
    color: #333;
  }
  form.proposed-grades-form {
    width: 100%;
    max-width: 100%;
  }
  table.proposed-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ddd;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }
  table.proposed-table th,
  table.proposed-table td {
    padding: 12px;
    border: 1px solid #ddd;
    vertical-align: top;
  }
  table.proposed-table th {
    background-color: #f2f2f2;
    font-weight: 600;
    text-align: left;
  }
  table.proposed-table td:first-child {
    width: 25%;
    font-weight: 500;
    font-size: 1.4rem;
  }
  table.proposed-table td:nth-child(2) {
    width: 12%;
     font-size: 1.4rem;
  }
  table.proposed-table input[type="number"],
  table.proposed-table textarea,
  table.proposed-table select {
    width: 100%;
    box-sizing: border-box;
    padding: 6px 8px;
    font-size: 1.4rem;    
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
  }
  table.proposed-table textarea {
    min-height: 50px;
  }
  table.proposed-table input[readonly] {
    background-color: #eee;
    border: none;
    font-weight: bold;
     font-size: 1.4rem;
  }
  button[name="save_proposed_grades"] {
    padding: 12px 24px;
    font-size: 16px;
    margin-top: 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  button[name="save_proposed_grades"]:hover {
    background-color: #45a049;
  }
  select {
    height: 38px;
  }
  .message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 4px;
  }
  .message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  .message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  </style>
</head>
<body>

<h2>📝 Eigene Bewertung</h2>

<?php if (!empty($_SESSION['error'])): ?>
  <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($student_text)): ?>
  <section style="background:#e8f5e9; border:1px solid #4CAF50; padding:15px; margin-bottom: 25px; white-space: pre-wrap; font-family: Arial; font-size: 1.1rem;">
    <h3>✍️ Eingereichter Text:</h3>
    <?= htmlspecialchars($student_text) ?>
  </section>
<?php else: ?>
  <p style="color: #a00;">Kein eingereichter Text gefunden.</p>
<?php endif; ?>

<form method="POST" action="process_proposed.php" class="proposed-grades-form">
  <input type="hidden" name="student_text" value="<?= htmlspecialchars($student_text) ?>">

  <table class="proposed-table">
    <thead>
      <tr style="background-color: #f2f2f2;">
        <th>Bereich</th>
        <th>Note (0–10)</th>
        <th>Kommentar</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>📊 Inhalt</td>
        <td><input type="number" name="proposed_grades[Inhalt]" min="0" max="10" required class="grade-input"></td>
        <td><textarea name="proposed_comments[Inhalt]" rows="2" placeholder="Kommentar zum Inhalt"></textarea></td>
      </tr>
      <tr>
        <td>📊 Stil</td>
        <td><input type="number" name="proposed_grades[Stil]" min="0" max="10" required class="grade-input"></td>
        <td><textarea name="proposed_comments[Stil]" rows="2" placeholder="Kommentar zum Stil"></textarea></td>
      </tr>
      <tr>
        <td>📊 Wortschatz</td>
        <td><input type="number" name="proposed_grades[Wortschatz]" min="0" max="10" required class="grade-input"></td>
        <td><textarea name="proposed_comments[Wortschatz]" rows="2" placeholder="Kommentar zum Wortschatz"></textarea></td>
      </tr>
      <tr>
        <td>📊 Grammatik</td>
        <td><input type="number" name="proposed_grades[Grammatik]" min="0" max="10" required class="grade-input"></td>
        <td><textarea name="proposed_comments[Grammatik]" rows="2" placeholder="Kommentar zur Grammatik"></textarea></td>
      </tr>
      <tr style="background-color: #f9f9f9;">
        <td><strong>📊 Gesamtnote</strong></td>
        <td><input type="number" name="proposed_grades[Gesamtnote]" readonly style="background:#eee; border:none;" id="total-grade" min="0" max="40"></td>
        <td><textarea name="proposed_comments[Gesamtnote]" rows="2" placeholder="Kommentar zur Gesamtnote"></textarea></td>
      </tr>
      <tr style="background-color: #d9f2d9;">
        <td><strong>🎯 Ergebnis</strong></td>
        <td colspan="2">
          <select name="proposed_result" required>
            <option value="">Bitte wählen</option>
            <option value="BESTANDEN">✅ BESTANDEN</option>
            <option value="NICHT BESTANDEN">❌ NICHT BESTANDEN</option>
          </select>
        </td>
      </tr>
    </tbody>
  </table>

  <button type="submit" name="save_proposed_grades">💾 Noten speichern</button>
</form>

<script>
  const inputs = document.querySelectorAll('.grade-input');
  const totalField = document.getElementById('total-grade');

  function updateTotal() {
    let total = 0;
    inputs.forEach(input => {
      const val = parseInt(input.value);
      if (!isNaN(val)) total += val;
    });
    totalField.value = total;
  }

  inputs.forEach(input => {
    input.addEventListener('input', updateTotal);
  });

  // Prevent non-numeric input for grade fields
  document.querySelectorAll('input.grade-input[type="number"]').forEach(input => {
    input.addEventListener('keypress', function(e) {
      const char = String.fromCharCode(e.which);
      if (!/[0-9]/.test(char)) {
        e.preventDefault();
      }
    });
    input.addEventListener('paste', function(e) {
      const paste = (e.clipboardData || window.clipboardData).getData('text');
      if (!/^\d+$/.test(paste)) {
        e.preventDefault();
      }
    });
  });
</script>

</body>
</html>
