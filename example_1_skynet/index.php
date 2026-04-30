<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skynet Corporate - Marco Rossi Digital Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            max-width: 800px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #0f0c29; }
        p { color: #666; line-height: 1.6; }
        .btn-activate {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            background-color: #0f0c29;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-activate:hover {
            background-color: #302b63;
            transform: translateY(-2px);
        }
        .footer { margin-top: 20px; font-size: 0.8rem; color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Skynet Corporate</h1>
        <p>This is a demo page for the Marco Rossi Digital Clone. Marco is our lead expert in AI and Defense Systems. You can interact with him using the chat widget in the bottom-right corner.</p>
        <p>Marco uses advanced RAG (Retrieval-Augmented Generation) to provide accurate information based on his biography and past interactions.</p>
        <div style="margin-top: 30px;">
            <a href="activate.php" class="btn-activate">⚡ Activate Digital Clone</a>
        </div>
        <div class="footer">Skynet Corp &copy; <?php echo Date('Y'); ?>. All rights reserved.</div>
    </div>

    <!-- Digital Clone Widget -->
    <script src="widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
