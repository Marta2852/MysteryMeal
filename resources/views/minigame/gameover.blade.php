<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAME OVER</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
            font-family: 'Courier New', monospace;
            color: #ff0000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }
        
        /* Retro scanlines effect */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(255, 0, 0, 0.03) 2px,
                rgba(255, 0, 0, 0.03) 4px
            );
            pointer-events: none;
            z-index: 1;
        }
        
        h1 {
            font-size: 4rem;
            color: #ff0000;
            text-shadow: 
                0 0 5px #ff0000,
                0 0 10px #ff0000,
                0 0 15px #ff0000,
                0 0 20px #ff0000;
            margin-bottom: 2rem;
            animation: flicker 2s infinite alternate;
            position: relative;
            z-index: 2;
        }
        
        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        p {
            font-size: 1.5rem;
            color: #ffff00;
            margin: 1rem 0;
            text-shadow: 0 0 5px #ffff00;
            position: relative;
            z-index: 2;
        }
        
        div {
            font-size: 1.2rem;
            color: #00ffff;
            margin: 1rem 0;
            text-shadow: 0 0 5px #00ffff;
            position: relative;
            z-index: 2;
        }
        
        a {
            margin-top: 2rem;
            padding: 15px 30px;
            background: linear-gradient(45deg, #ff0000, #ff6600);
            color: #ffffff;
            text-decoration: none;
            border-radius: 0;
            border: 3px solid #ffff00;
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 
                0 0 10px #ff0000,
                inset 0 0 10px rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        a:hover {
            background: linear-gradient(45deg, #ff6600, #ff0000);
            box-shadow: 
                0 0 20px #ff0000,
                0 0 30px #ff0000,
                inset 0 0 10px rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        /* Retro border effect */
        .retro-border {
            border: 4px solid #ffff00;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            box-shadow: 
                0 0 20px rgba(255, 255, 0, 0.3),
                inset 0 0 20px rgba(255, 255, 0, 0.1);
            position: relative;
            z-index: 2;
        }
    </style>
    @vite('resources/js/minigame.js')
</head>
<body>
    <div class="retro-border">
        <h1>GAME OVER</h1>

        <p>Your score: {{ request('score', 'Not recorded') }}</p>
        @php
            $time = request('time', 0);
            $minutes = floor($time / 60);
            $seconds = $time % 60;
            $timeFormatted = sprintf('%02d:%02d', $minutes, $seconds);
        @endphp
        <p>Your time: {{ $timeFormatted }}</p>
        <p>Your all-time high score: {{ $highScore }}</p>
        @php
            $longestMinutes = floor($longestTime / 60);
            $longestSeconds = $longestTime % 60;
            $longestTimeFormatted = sprintf('%02d:%02d', $longestMinutes, $longestSeconds);
        @endphp
        <p>Your longest time: {{ $longestTimeFormatted }}</p>
        <div>{{ Auth::user()->name }}</div>
        <a href="/minigame">Try Again</a>
    </div>
</body>
</html>
