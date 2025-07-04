window.addEventListener('load', () => {
  const studentText = document.getElementById('student_text');
  if (!studentText) return;

  const offset = 20;
  const targetPosition = studentText.getBoundingClientRect().top + window.pageYOffset - offset;
  const startPosition = window.pageYOffset;
  const distance = targetPosition - startPosition;
  const duration = 1500; // scroll duration in ms (1.5 seconds)

  let startTime = null;

  function smoothScroll(timestamp) {
    if (!startTime) startTime = timestamp;
    const timeElapsed = timestamp - startTime;
    const progress = Math.min(timeElapsed / duration, 1);

    // Ease in/out function (optional for smoothness)
    const ease = progress < 0.5
      ? 2 * progress * progress
      : -1 + (4 - 2 * progress) * progress;

    window.scrollTo(0, startPosition + distance * ease);

    if (progress < 1) {
      requestAnimationFrame(smoothScroll);
    } else {
      // Scroll finished, linger for 1 second, then focus textarea
      setTimeout(() => {
        studentText.focus();
      }, 1000); // 1000ms linger
    }
  }

  requestAnimationFrame(smoothScroll);
});
