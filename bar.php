<style>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
    font-family: Arial, sans-serif;
  }

  .loader {
    border: 8px solid #f3f3f3;
    border-top: 8px solid #3498db;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
  }

  .overlay p {
    margin-top: 20px;
    font-size: 1.2rem;
    color: #333;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<div class="overlay" id="loadingOverlay">
  <div class="loader"></div>
  <p>Bitte warten... Ihr Text wird analysiert.</p>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form[action='process.php']");
    const overlay = document.getElementById("loadingOverlay");
    const submitButton = form.querySelector("button[type='submit']");

    if (!form || !overlay || !submitButton) return;

    let isSubmitting = false;

    form.addEventListener("submit", function (e) {
      if (isSubmitting) {
        e.preventDefault(); // prevent double submission
        return;
      }

      isSubmitting = true;
      overlay.style.display = "flex";

      submitButton.disabled = true;
      submitButton.textContent = "Bitte warten...";
      submitButton.style.backgroundColor = "#aaa";
      submitButton.style.cursor = "not-allowed";
    });
  });
</script>
