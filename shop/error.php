<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Temporarily Unavailable - GoldenDream</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ffd600;
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        .retry-btn {
            background: #ffd600;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .retry-btn:hover {
            background: #ffe066;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🛍️</div>
        <h1>Shop Temporarily Unavailable</h1>
        <p>We're experiencing some technical difficulties with our shop. Please try again in a few moments.</p>
        <p>If the problem persists, please contact our support team.</p>
        <a href="index.php" class="retry-btn">Try Again</a>
    </div>
</body>
</html> 