<?php
/**
 * Setup Utility for OpenAI API Key
 * Use this script to encode your API key before placing it in api_config.php
 */

require_once 'api_config.php';

$key_to_encode = '';
if (isset($_POST['api_key'])) {
    $key_to_encode = $_POST['api_key'];
} elseif (isset($_GET['api_key'])) {
    $key_to_encode = $_GET['api_key'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenAI API Key Encoder</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            max-width: 800px; 
            margin: 40px auto; 
            padding: 20px; 
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #0f0c29; border-bottom: 2px solid #00d2ff; padding-bottom: 10px; }
        .warning { background: #fff5f5; border: 1px solid #feb2b2; padding: 15px; color: #c53030; margin-bottom: 20px; border-radius: 8px; }
        .result { background: #ebf8ff; border: 1px solid #90cdf4; padding: 15px; font-family: monospace; word-break: break-all; margin: 20px 0; border-radius: 8px; color: #2c5282; }
        form { margin-top: 20px; }
        input[type="text"] { width: 100%; padding: 12px; box-sizing: border-box; border: 1px solid #cbd5e0; border-radius: 8px; margin-bottom: 10px; }
        input[type="submit"] { 
            background: linear-gradient(to right, #00d2ff, #3a7bd5);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
        }
        code { background: #edf2f7; padding: 2px 4px; border-radius: 4px; }
        .instructions { background: #f7fafc; padding: 20px; border-radius: 8px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>OpenAI API Key Encoder</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This script is for initial setup only. 
            <strong>DELETE THIS FILE FROM YOUR SERVER IMMEDIATELY</strong> after use.
            Leaving it online exposes a potential security risk.
        </div>

        <?php if ($key_to_encode !== ''): ?>
            <?php $encoded = encodeKey($key_to_encode); ?>
            <h2>Your Encoded Key</h2>
            <p>Copy the line below and paste it into <code>api_config.php</code> around line 37:</p>
            <div class="result">
                $OBFUSCATED_API_KEY = '<?php echo htmlspecialchars($encoded); ?>';
            </div>
            <p><a href="setup-key.php" style="color: #3a7bd5; text-decoration: none;">&larr; Encode another key</a></p>
        <?php else: ?>
            <p>Enter your OpenAI API key below to encode it using the XOR + Base64 method required by this application.</p>
            <form method="post" action="setup-key.php">
                <input type="text" name="api_key" placeholder="sk-..." required autofocus>
                <input type="submit" value="Encode Key">
            </form>
            
            <p style="font-size: 0.9rem; color: #718096; margin-top: 15px;">
                Alternatively, use GET: <code>setup-key.php?api_key=sk-...</code>
            </p>
        <?php endif; ?>

        <div class="instructions">
            <h3>How to use:</h3>
            <ol>
                <li>Paste your <strong>OpenAI API Key</strong> in the field above and click <strong>Encode</strong>.</li>
                <li>Copy the generated <code>$OBFUSCATED_API_KEY</code> line.</li>
                <li>Open <code>api_config.php</code> in your file manager or via FTP.</li>
                <li>Find the line <code>$OBFUSCATED_API_KEY = '';</code> and replace it with your copied line.</li>
                <li>Save the file.</li>
                <li><strong>IMPORTANT:</strong> Delete <code>setup-key.php</code> from your server.</li>
            </ol>
        </div>
    </div>
</body>
</html>
