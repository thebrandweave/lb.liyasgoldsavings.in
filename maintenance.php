<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Dream - Under Maintenance</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ECE9E6;  /* fallback for old browsers */
background: -webkit-linear-gradient(to right, #FFFFFF, #ECE9E6);  /* Chrome 10-25, Safari 5.1-6 */
background: linear-gradient(to right, #FFFFFF, #ECE9E6); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .container {
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            overflow-y: auto;
            position: relative;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg,rgb(4, 4, 4), #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .logo p {
            color: #666;
            font-size: 1.1rem;
            font-weight: 300;
        }

        .maintenance-animation {
            margin-bottom: 30px;
            position: relative;
        }

        .fixing-gif {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
        }

        .fixing-gif img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
        }

        .fallback-icon {
            font-size: 4rem;
            color: white;
            animation: fixing 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fixing {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(5deg) scale(1.1); }
            50% { transform: rotate(0deg) scale(1); }
            75% { transform: rotate(-5deg) scale(1.1); }
            100% { transform: rotate(0deg) scale(1); }
        }

        .sparkles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
        }

        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #67D6BE;
            border-radius: 50%;
            animation: sparkle 2s linear infinite;
        }

        .sparkle:nth-child(1) { top: 20%; left: 20%; animation-delay: 0s; }
        .sparkle:nth-child(2) { top: 30%; right: 25%; animation-delay: 0.5s; }
        .sparkle:nth-child(3) { bottom: 30%; left: 30%; animation-delay: 1s; }
        .sparkle:nth-child(4) { bottom: 20%; right: 20%; animation-delay: 1.5s; }

        @keyframes sparkle {
            0% { opacity: 0; transform: scale(0); }
            50% { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(0); }
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: 600;
        }

        p {
            margin-bottom: 25px;
            line-height: 1.6;
            color: #555;
            font-size: 1.1rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 10px;
            margin: 30px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #67D6BE,rgb(88, 85, 92));
            border-radius: 10px;
            animation: progress 3s ease-in-out infinite;
        }

        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        .status {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin: 25px 0;
            border-left: 4px solid #67D6BE;
        }

        .status h3 {
            color:rgb(15, 15, 15);
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .contact {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .contact p {
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color:rgb(0, 0, 0);
            font-weight: 500;
        }

        .contact-item i {
            font-size: 1.2rem;
        }

      

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 2rem;
            }

            .fixing-gif {
                width: 250px;
                height: 250px;
            }

            .fixing-gif img {
                width: 150px;
                height: 150px;
            }

            .fallback-icon {
                font-size: 3rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Golden Dream</h1>
            <p>Your Financial Future Starts Here</p>
        </div>

        <div class="maintenance-animation">
            <div class="fixing-gif">
                
                <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExY2Q5MzZ0NG5zNGM3dWFjbmc4ZHJ4aXlrcnc3YTBueWF2eXA5OHV5dyZlcD12MV9naWZzX3NlYXJjaCZjdD1n/XZrUw9cTqJ0vi8bmV9/giphy.gif" alt="Fixing Animation" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <i class="fas fa-tools fallback-icon" style="display: none;"></i>
            </div>
            <div class="sparkles">
                <div class="sparkle"></div>
                <div class="sparkle"></div>
                <div class="sparkle"></div>
                <div class="sparkle"></div>
            </div>
        </div>

        <h2>Website Under Maintenance</h2>
        <p>We are currently performing scheduled maintenance to improve our services and enhance your experience.</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <div class="status">
            <h3><i class="fas fa-clock"></i> Estimated Completion</h3>
            <p>We expect to be back online within the next few hours. Thank you for your patience.</p>
        </div>

        <div class="contact">
            <p><strong>Need immediate assistance?</strong></p>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>goldendream175@gmail.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+91 73497 39580</span>
                </div>
            </div>
            
          
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds to check if maintenance is over
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Show fallback icon if GIF fails to load
        document.addEventListener('DOMContentLoaded', function() {
            const gifImg = document.querySelector('.fixing-gif img');
            const fallbackIcon = document.querySelector('.fallback-icon');
            
            gifImg.addEventListener('error', function() {
                this.style.display = 'none';
                fallbackIcon.style.display = 'block';
            });
        });
    </script>
</body>
</html>
