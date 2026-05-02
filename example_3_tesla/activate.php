<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Tesla - Wardenclyffe Protocol</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000b1a, #001f3f, #000b1a);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0, 116, 217, 0.37);
            border: 1px solid rgba(0, 116, 217, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        h1 {
            color: #FFD700;
            margin-bottom: 1rem;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255,215,0,0.5);
        }
        p {
            color: #ccc;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #7FDBFF;
            font-size: 0.85rem;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(0, 116, 217, 0.4);
            border-radius: 8px;
            color: white;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            border-color: #7FDBFF;
        }
        button {
            background: linear-gradient(to right, #0074D9, #00BFFF);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            text-transform: uppercase;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 116, 217, 0.4);
        }
        button:disabled {
            background: #444;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Toast Styles */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .toast {
            background: #001f3f;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 250px;
            animation: slideIn 0.3s ease-out;
            border: 1px solid rgba(0, 116, 217, 0.3);
        }
        .toast.success { border-left: 5px solid #FFD700; }
        .toast.error { border-left: 5px solid #ff4444; }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>
    <div id="toast-container"></div>

    <div class="card">
        <h1>Tesla Activation</h1>
        <p>Integrate the World Wireless System. Enter your OpenAI API Key to index the Nikola Tesla biography and initialize the digital clone.</p>
        
        <form id="activationForm" action="indexBio.php" method="POST">
            <div class="form-group">
                <label for="apiKey">OPENAI API KEY</label>
                <input type="password" id="apiKey" name="apiKey" placeholder="sk-..." required>
            </div>
            <button type="submit" id="submitBtn">Initialize Wardenclyffe</button>
                
        </form>
            <button type="button" id="gobackBtn" style="margin-top: 10px; background: rgba(255,255,255,0.1);" disabled>Back to Lab</button>
    </div>

    <script>
        const form = document.getElementById('activationForm');
        const submitBtn = document.getElementById('submitBtn');
        const gobackBtn = document.getElementById('gobackBtn');
        const toastContainer = document.getElementById('toast-container');

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 5000);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const apiKey = document.getElementById('apiKey').value;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            try {
                const response = await fetch('indexBio.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `apiKey=${encodeURIComponent(apiKey)}`
                });
                
                const result = await response.json();
                
                if (response.ok && result.status === 'success') {
                    showToast(result.message || 'Activation successful!', 'success');
                        submitBtn.disabled = true;
                        gobackBtn.disabled = false;
                } else {
                    showToast(result.message || 'Activation failed!', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Initialize Wardenclyffe';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred during activation.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Initialize Wardenclyffe';
            }
        });
        
        gobackBtn.addEventListener('click', () => {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>
