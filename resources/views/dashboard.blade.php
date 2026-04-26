<!DOCTYPE html>
<html>
<head>
    <title>Recipe Finder - Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
        }
        
        .welcome-section h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 1px solid #ddd;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .card h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .card-btn {
            display: inline-block;
            padding: 8px 20px;
            background: #4CAF50;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .recipes-card .card-btn {
            background: #e74c3c;
        }
        
        .recipes-card .card-btn:hover {
            background: #c0392b;
        }
        
        .minigame-card .card-btn {
            background: #27ae60;
        }
        
        .minigame-card .card-btn:hover {
            background: #219a52;
        }
        
        .logout-section {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            border: 1px solid #ddd;
        }
        
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #666;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .logout-btn:hover {
            background: #555;
        }
        
        @media (max-width: 768px) {
            .cards-container {
                grid-template-columns: 1fr;
            }
            
            .welcome-section h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>🍽️ Welcome to Recipe Finder!</h1>
            <p>Discover delicious recipes or play a fun word guessing game</p>
        </div>
        
        <div class="cards-container">
            <a href="{{ route('recipes.index') }}" class="card recipes-card">
                <div class="card-icon">🍳</div>
                <h2>Recipes</h2>
                <p>Search for recipes based on ingredients you have. Find what you can cook with your available products!</p>
                <span class="card-btn">Explore Recipes →</span>
            </a>
            
            <a href="{{ route('minigame.index') }}" class="card minigame-card">
                <div class="card-icon">🎮</div>
                <h2>Minigame</h2>
                <p>Play the word guessing game! Try to guess the food-related word before you run out of attempts.</p>
                <span class="card-btn">Play Now →</span>
            </a>
        </div>
        
        @auth
        <div class="logout-section">
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="logout-btn">🚪 Logout</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
        @endauth
        
        @guest
        <div class="logout-section">
            <a href="{{ route('login') }}" class="logout-btn">🔐 Login</a>
            <a href="{{ route('register') }}" class="logout-btn">📝 Register</a>
        </div>
        @endguest
    </div>
</body>
</html>