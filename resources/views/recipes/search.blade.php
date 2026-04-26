<!DOCTYPE html>
<html>
<head>
    <title>Recipe Search</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div style="margin-bottom: 20px;">
    <a href="{{ route('dashboard') }}" style="display: inline-block; padding: 10px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px;">
        ← Back to Dashboard
    </a>
</div>
    

<div class="container">

    <h1>Recipe Finder</h1>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    {{-- NAV --}}
    <div class="top-bar">
        <a href="/recipes/favorites">⭐ Favorites</a>
        <a href="/recipes/create">➕ Add Recipe</a>
    </div>

    {{-- ADD PRODUCT --}}
    <form method="POST" action="/recipes/add" class="search-form">
        @csrf
        <input type="text" name="product" placeholder="Enter product..." required>
        <button type="submit">Add</button>
    </form>

    {{-- PRODUCTS --}}
    <h3>Your Products</h3>

    <ul class="product-list">
        @foreach($products as $index => $product)
            <li>
                {{ $product }}

                <form method="POST" action="/recipes/remove">
                    @csrf
                    <input type="hidden" name="index" value="{{ $index }}">
                    <button type="submit">X</button>
                </form>
            </li>
        @endforeach
    </ul>

    {{-- SEARCH --}}
    <form method="POST" action="/recipes/search">
        @csrf
        <button class="search-btn">Search Recipes</button>
    </form>

    {{-- USER RECIPES --}}
    @if(!empty($user_recipes))
        <h3>Your Recipes</h3>

        <div class="accordion">

            @foreach($user_recipes as $recipe)

                <details class="accordion-item">

                    <summary class="accordion-btn">
                        {{ $recipe['name'] }}
                    </summary>

                    <div class="accordion-content">

                        <p><strong>Ingredients:</strong></p>
                        <ul>
                            @foreach($recipe['ingredients'] as $ing)
                                <li>{{ $ing }}</li>
                            @endforeach
                        </ul>

                        <p><strong>Instructions:</strong></p>
                        <p>{{ $recipe['instructions'] }}</p>

                    </div>

                </details>

            @endforeach

        </div>
    @endif

</div>

</body>
</html>