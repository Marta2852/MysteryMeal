<!DOCTYPE html>
<html>
<head>
    <title>Favorites</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .top-bar {
            margin-bottom: 20px;
        }
        .top-bar a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .recipes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .recipe-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .recipe-card h3 {
            margin-top: 0;
            color: #333;
        }
        .recipe-card img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .ingredients {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .have {
            background-color: #d4edda;
            padding: 10px;
            border-radius: 5px;
        }
        .have h4 {
            margin-top: 0;
            color: #155724;
        }
        .missing {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
        }
        .missing h4 {
            margin-top: 0;
            color: #721c24;
        }
        .ingredients ul {
            margin: 0;
            padding-left: 20px;
        }
        .ingredients li {
            margin: 5px 0;
        }
        .remove-btn {
            margin-top: 15px;
            padding: 8px 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .debug-info {
            background: #e0e0e0;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            border-radius: 5px;
        }
        .debug-info strong {
            color: #333;
        }
        @media (max-width: 768px) {
            .recipes {
                grid-template-columns: 1fr;
            }
            .ingredients {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <h2>⭐ Favorites</h2>

    <div class="top-bar">
        <a href="/recipes/search">⬅ Back to Search</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="recipes">

        @if(!empty($favorites))

            @foreach($favorites as $meal)

                <div class="recipe-card">

                    <h3>{{ $meal['name'] ?? 'Unknown' }}</h3>

                    <img src="{{ $meal['thumb'] ?? '' }}" alt="{{ $meal['name'] ?? 'Recipe' }}">

                    <div class="ingredients">

                        <div class="have">
                            <h4>✅ You have:</h4>
                            <ul>
                                @forelse(($meal['have_ingredients'] ?? []) as $item)
                                    <li>{{ ucfirst($item) }}</li>
                                @empty
                                    <li>No matching ingredients</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="missing">
                            <h4>🛒 Missing:</h4>
                            <ul>
                                @forelse(($meal['missing_ingredients'] ?? []) as $item)
                                    <li>{{ ucfirst($item) }}</li>
                                @empty
                                    <li>You have all ingredients! 🎉</li>
                                @endforelse
                            </ul>
                        </div>

                    </div>


                    <form action="{{ route('recipes.removeFavorite') }}" method="POST">
                        @csrf
                        <input type="hidden" name="meal_id" value="{{ $meal['id'] }}">
                        <button type="submit" class="remove-btn" onclick="return confirm('Remove this recipe from favorites?')">Remove from Favorites</button>
                    </form>

                </div>

            @endforeach

        @else
            <p>No favorites yet. Go back and add some recipes to your favorites!</p>
        @endif

    </div>

</div>

</body>
</html>