// typewriter.js
document.addEventListener('DOMContentLoaded', () => {
    const correctedDiv = document.getElementById('corrected-text');
    if (!correctedDiv) return;

    const fullText = correctedDiv.getAttribute('data-fulltext');
    correctedDiv.innerHTML = ''; // clear container

    let i = 0;

    function typeWriter() {
        if (i < fullText.length) {
            let char = fullText.charAt(i);
            if (char === '\n') {
                correctedDiv.innerHTML += '<br>';
            } else {
                correctedDiv.innerHTML += char;
            }
            i++;
            setTimeout(typeWriter, 3);
        }
    }
    typeWriter();
});
