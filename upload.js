document.addEventListener('DOMContentLoaded', function () {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('image-upload');
    const textarea = document.getElementById('student_text');

    if (!uploadArea || !fileInput || !textarea) return;

    // Click on the upload area triggers file selector
    uploadArea.addEventListener('click', () => fileInput.click());

    // Handle file selection
    fileInput.addEventListener('change', e => {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    });

    // Handle drag & drop
    uploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#e0ffe0';
    });
    uploadArea.addEventListener('dragleave', e => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '';
    });
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '';
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    // OCR processing
    function handleFile(file) {
        if (!file.type.startsWith('image/')) {
            alert('Bitte eine Bilddatei hochladen.');
            return;
        }
        uploadArea.textContent = 'OCR läuft, bitte warten...';

        Tesseract.recognize(
            file,
            'deu', // Language: German
            { logger: m => console.log(m) }
        ).then(({ data: { text } }) => {
            textarea.value = text.trim();
            uploadArea.textContent = '📂 Ziehen Sie Ihr Bild hierher oder klicken zum Auswählen';
        }).catch(() => {
            alert('OCR fehlgeschlagen. Bitte versuchen Sie es erneut.');
            uploadArea.textContent = '📂 Ziehen Sie Ihr Bild hierher oder klicken zum Auswählen';
        });
    }
});
