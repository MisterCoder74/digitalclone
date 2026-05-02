<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesla Wardenclyffe - Nikola Tesla Digital Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000b1a;
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
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,116,217,0.3);
            border: 1px solid rgba(0,116,217,0.2);
        }
        h1 { color: #FFD700; text-shadow: 0 0 10px rgba(255,215,0,0.5); }
        p { color: #ccc; line-height: 1.6; }
        .btn-activate {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            background-color: #0074D9;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            border: 1px solid #7FDBFF;
        }
        .btn-activate:hover {
            background-color: #00BFFF;
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(127,219,255,0.5);
        }
        .footer { margin-top: 20px; font-size: 0.8rem; color: #555; }
        .tesla-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #FFD700;
            margin-bottom: 20px;
            object-fit: cover;
            box-shadow: 0 0 20px rgba(255,215,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="tesla.jpg" alt="Nikola Tesla" class="tesla-image" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/d/d4/N.Tesla.JPG'">
        <h1>Tesla Wardenclyffe Protocol</h1>
        <p>Welcome to the digital reincarnation of Nikola Tesla. This project aims to preserve the knowledge, personality, and vision of the man who lit the world.</p>
        <p>Nikola uses advanced RAG (Retrieval-Augmented Generation) to provide insights on electricity, wireless transmission, and his various inventions based on his comprehensive biography.</p>
        <div style="margin-top: 30px;">
            <a href="activate.php" class="btn-activate">⚡ Initialize Tesla Clone</a>
        </div>
        <div class="footer">Wardenclyffe Lab &copy; <?php echo Date('Y'); ?>. All rights reserved.</div>
    </div>

    <!-- Digital Clone Widget -->
    <script src="widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
