<!DOCTYPE html>
<html>
<head>
    <title>Recipe Results</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .top-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar a, .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .products-list {
            background: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .products-list span {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            margin: 5px;
            display: inline-block;
            border-radius: 3px;
        }
        .time-filter {
            background: #f0f0f0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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
        .time-info {
            margin: 10px 0;
            color: #666;
            font-size: 14px;
            padding: 8px;
            background: #e8e8e8;
            border-radius: 5px;
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
        .favorite-btn {
            margin-top: 15px;
            padding: 8px 12px;
            background-color: #ffc107;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .favorite-btn:hover {
            background-color: #ffca2c;
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
        .no-results {
            text-align: center;
            padding: 40px;
            background: #f9f9f9;
            border-radius: 10px;
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

    <div class="top-bar">
        <a href="{{ route('recipes.index') }}">⬅ Back to Search</a>
        <a href="{{ route('recipes.favorites') }}">⭐ My Favorites</a>
    </div>

    <div class="products-list">
        <strong>Your products:</strong>
        @foreach($products as $product)
            <span>{{ $product }}</span>
        @endforeach
    </div>

    <!-- Time Filter Form -->
    <div class="time-filter">
        <form action="{{ route('recipes.search') }}" method="POST">
            @csrf
            <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="max_time" style="display: block; margin-bottom: 5px; font-weight: bold;">⏱️ Maximum Total Time:</label>
                    <select name="max_time" id="max_time" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">Any time</option>
                        <option value="15" {{ ($selectedMaxTime ?? '') == '15' ? 'selected' : '' }}>≤ 15 minutes</option>
                        <option value="30" {{ ($selectedMaxTime ?? '') == '30' ? 'selected' : '' }}>≤ 30 minutes</option>
                        <option value="45" {{ ($selectedMaxTime ?? '') == '45' ? 'selected' : '' }}>≤ 45 minutes</option>
                        <option value="60" {{ ($selectedMaxTime ?? '') == '60' ? 'selected' : '' }}>≤ 1 hour</option>
                        <option value="90" {{ ($selectedMaxTime ?? '') == '90' ? 'selected' : '' }}>≤ 1.5 hours</option>
                        <option value="120" {{ ($selectedMaxTime ?? '') == '120' ? 'selected' : '' }}>≤ 2 hours</option>
                    </select>
                </div>
                <div>
                    <button type="submit" style="background-color: #4CAF50; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer;">Filter</button>
                </div>
                @if($selectedMaxTime ?? false)
                    <div>
                        <a href="{{ route('recipes.search') }}" style="display: inline-block; background-color: #666; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">Clear Filter</a>
                    </div>
                @endif
            </div>
        </form>
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
        @if(!empty($meals))
            @foreach($meals as $meal)
                <div class="recipe-card">
                    <h3>{{ $meal['strMeal'] }}</h3>
                    
                    <img src="{{ $meal['strMealThumb'] }}" alt="{{ $meal['strMeal'] }}">
                    
                    <div class="time-info">
                        ⏱️ <strong>Total time:</strong> {{ $meal['total_time'] ?? 'N/A' }}
                        @if(isset($meal['prep_time']) && $meal['prep_time'] != 'N/A')
                            <span style="margin-left: 10px;">📋 Prep: {{ $meal['prep_time'] }}</span>
                        @endif
                        @if(isset($meal['cook_time']) && $meal['cook_time'] != 'N/A')
                            <span style="margin-left: 10px;">🔥 Cook: {{ $meal['cook_time'] }}</span>
                        @endif
                    </div>
                    
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
                    
                    <form action="{{ route('recipes.addFavorite') }}" method="GET">
    @csrf
    <input type="hidden" name="meal_id" value="{{ $meal['idMeal'] }}">
    <button type="submit" class="favorite-btn">⭐ Add to Favorites</button>
</form>
                </div>
            @endforeach
        @else
            <div class="no-results">
                <h3>No recipes found 😢</h3>
                <p>Try adding different products to your list or adjust the time filter.</p>
            </div>
        @endif
    </div>
</div>

</body>
</html>