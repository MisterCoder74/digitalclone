<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activate Clone | Digital Clone</title>
  <style>
    :root {
      --bg-gradient: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      --accent-gradient: linear-gradient(90deg, #4facfe, #00f2fe);
      --glass-bg: rgba(255, 255, 255, 0.05);
      --glass-border: rgba(255, 255, 255, 0.1);
      --text-main: #ffffff;
      --text-dim: rgba(255, 255, 255, 0.7);
      --text-muted: rgba(255, 255, 255, 0.5);
      --error: #ff4b2b;
      --success: #00f2a0;
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--bg-gradient);
      background-attachment: fixed;
      color: var(--text-main);
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 500px;
    }

    .activation-card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(15px);
      padding: 40px;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
      text-align: center;
      animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 800;
      background: var(--accent-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-decoration: none;
      margin-bottom: 30px;
      display: inline-block;
    }

    h2 {
      font-size: 1.5rem;
      margin-bottom: 10px;
    }

    p {
      color: var(--text-dim);
      font-size: 0.95rem;
      margin-bottom: 30px;
    }

    .form-group {
      text-align: left;
      margin-bottom: 25px;
    }

    label {
      display: block;
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #4facfe;
      margin-bottom: 8px;
      margin-left: 5px;
    }

    input[type="password"] {
      width: 100%;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      color: #fff;
      padding: 12px 16px;
      font-size: 1rem;
      outline: none;
      transition: all 0.3s;
    }

    input:focus {
      border-color: #4facfe;
      background: rgba(255, 255, 255, 0.1);
      box-shadow: 0 0 15px rgba(79, 172, 254, 0.2);
    }

    .btn {
      width: 100%;
      padding: 14px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      border: none;
      transition: all 0.3s;
      background: var(--accent-gradient);
      color: #0f0c29;
      box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
    }

    .btn:active {
      transform: translateY(0);
    }

    .btn:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    .back-link {
      display: inline-block;
      margin-top: 25px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: var(--text-main);
    }

    /* Toast styles */
    #toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%) translateY(100px);
      padding: 16px 24px;
      border-radius: 12px;
      background: rgba(30, 30, 50, 0.95);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 1000;
      transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 320px;
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
    }

    #toast.show {
      transform: translateX(-50%) translateY(0);
    }

    #toast.success {
      border-bottom: 4px solid var(--success);
    }

    #toast.error {
      border-bottom: 4px solid var(--error);
    }

    .toast-icon {
      font-size: 1.2rem;
    }

    /* Loader */
    .loader {
      display: none;
      width: 18px;
      height: 18px;
      border: 2px solid rgba(15, 12, 41, 0.3);
      border-radius: 50%;
      border-top-color: #0f0c29;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .btn.loading .loader {
        display: block;
    }

  </style>
</head>
<body>

  <div class="container">
    <div class="activation-card">
      <a href="index.html" class="logo">DIGITAL CLONE</a>
      <h2>Activate Your Clone</h2>
      <p>Enter your OpenAI API key to index the biography and activate the digital persona.</p>
      
      <div class="form-group">
        <label for="apiKey">OpenAI API Key</label>
        <input type="password" id="apiKey" placeholder="sk-..." autocomplete="off">
      </div>
      
      <button id="activateBtn" class="btn">
        <span class="btn-text">Activate & Index Biography</span>
        <div class="loader"></div>
      </button>
      
      <a href="index.html" class="back-link">← Back to Home</a>
    </div>
  </div>

  <div id="toast">
    <span class="toast-icon"></span>
    <span class="toast-message"></span>
  </div>

  <script>
    const activateBtn = document.getElementById('activateBtn');
    const btnText = activateBtn.querySelector('.btn-text');
    const apiKeyInput = document.getElementById('apiKey');
    const toast = document.getElementById('toast');
    const toastIcon = toast.querySelector('.toast-icon');
    const toastMessage = toast.querySelector('.toast-message');

    // Load key from localStorage if exists
    const savedKey = localStorage.getItem('openaikey');
    if (savedKey) {
      apiKeyInput.value = savedKey;
    }

    let toastTimeout;
    function showToast(message, type = 'success') {
      clearTimeout(toastTimeout);
      toast.className = 'show ' + type;
      toastMessage.textContent = message;
      toastIcon.textContent = type === 'success' ? '✅' : '❌';
      
      toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
      }, 5000);
    }

    activateBtn.addEventListener('click', async () => {
      const apiKey = apiKeyInput.value.trim();
      
      if (!apiKey) {
        showToast('Please enter an OpenAI API Key', 'error');
        return;
      }

      // Save key to localStorage
      localStorage.setItem('openaikey', apiKey);

      // UI Loading state
      activateBtn.disabled = true;
      activateBtn.classList.add('loading');
      btnText.textContent = 'Processing...';

      try {
        // Using fetch to indexBio.php with POST
        const response = await fetch('indexBio.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `apiKey=${encodeURIComponent(apiKey)}`
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        
        const data = await response.json();

        if (data.status === 'success') {
          showToast(`Success! ${data.message} (${data.files_processed} files processed)`, 'success');
          // Update button text to show completion
          btnText.textContent = 'Successfully Indexed';
          setTimeout(() => {
              btnText.textContent = 'Activate & Index Biography';
          }, 3000);
        } else {
          showToast(data.message || 'An error occurred during activation', 'error');
          btnText.textContent = 'Activate & Index Biography';
        }
      } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Network error. Please try again.', 'error');
        btnText.textContent = 'Activate & Index Biography';
      } finally {
        activateBtn.disabled = false;
        activateBtn.classList.remove('loading');
      }
    });

    // Support Enter key
    apiKeyInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        activateBtn.click();
      }
    });
  </script>
</body>
</html>
