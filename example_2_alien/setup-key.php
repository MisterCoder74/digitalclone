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
    <title>OpenAI API Key Encoder - Galactic Dominion</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            max-width: 800px; 
            margin: 40px auto; 
            padding: 20px; 
            background-color: #000;
            color: #adb5bd;
        }
        .container {
            background: #0d1b2a;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 255, 65, 0.1);
            border: 1px solid rgba(0, 255, 65, 0.2);
        }
        h1 { color: #00ff41; border-bottom: 2px solid #008f11; padding-bottom: 10px; text-transform: uppercase; }
        .warning { background: rgba(255, 68, 68, 0.1); border: 1px solid #ff4444; padding: 15px; color: #ff4444; margin-bottom: 20px; border-radius: 8px; }
        .result { background: rgba(0, 255, 65, 0.05); border: 1px solid #00ff41; padding: 15px; font-family: monospace; word-break: break-all; margin: 20px 0; border-radius: 8px; color: #00ff41; }
        form { margin-top: 20px; }
        input[type="text"] { width: 100%; padding: 12px; box-sizing: border-box; border: 1px solid rgba(0, 255, 65, 0.3); border-radius: 8px; margin-bottom: 10px; background: #000; color: #00ff41; }
        input[type="submit"] { 
            background: linear-gradient(to right, #00ff41, #008f11);
            color: #000;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        code { background: rgba(255, 255, 255, 0.1); padding: 2px 4px; border-radius: 4px; color: #fff; }
        .instructions { background: rgba(255, 255, 255, 0.03); padding: 20px; border-radius: 8px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Galactic API Encoder</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This subspace link is for initial setup only. 
            <strong>TERMINATE THIS DATA STREAM</strong> after use.
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
            <h3>Protocol:</h3>
            <ol>
                <li>Input your <strong>OpenAI API Key</strong> above.</li>
                <li>Capture the generated <code>$OBFUSCATED_API_KEY</code> string.</li>
                <li>Infiltrate <code>api_config.php</code>.</li>
                <li>Replace the empty <code>$OBFUSCATED_API_KEY</code> with your captured string.</li>
                <li>Save the file.</li>
                <li><strong>CRITICAL:</strong> Terminate <code>setup-key.php</code> from the server immediately.</li>
            </ol>
        </div>

    </div>
</body>
</html>
