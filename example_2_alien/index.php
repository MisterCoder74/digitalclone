<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galactic Dominion - Commander Zax Xor'than</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000;
            background-image: radial-gradient(circle at center, #1a1a2e 0%, #000 100%);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #fff;
        }
        .container {
            text-align: center;
            max-width: 800px;
            padding: 40px;
            background: rgba(0, 255, 65, 0.05);
            border: 1px solid rgba(0, 255, 65, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 255, 65, 0.1);
            backdrop-filter: blur(5px);
        }
        h1 { color: #00ff41; text-transform: uppercase; letter-spacing: 3px; }
        p { color: #adb5bd; line-height: 1.6; }
        .btn-activate {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            background-color: #00ff41;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
            text-transform: uppercase;
        }
        .btn-activate:hover {
            background-color: #008f11;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.5);
        }
        .footer { margin-top: 20px; font-size: 0.8rem; color: #444; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Galactic Dominion</h1>
        <p>This is the official landing page for the Earth Acquisition Campaign. Commander Zax Xor'than is leading the operation. You may attempt to communicate with him using the subspace link in the bottom-right corner.</p>
        <p>The Commander uses advanced Andromedan RAG technology to access his extensive tactical biography and past human intercepts.</p>
        <div style="margin-top: 30px;">
            <a href="activate.php" class="btn-activate">⚡ Establish Subspace Link</a>
        </div>
        <div class="footer">Galactic Dominion &copy; <?php echo Date('Y'); ?>. All your base are belong to us.</div>
    </div>

    <!-- Digital Clone Widget -->
    <script src="widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
