<!DOCTYPE html>
<html>
<head>
    <title>Recipe Finder</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div class="container">

    <div class="card">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Products</h2>
            <form method="POST" action="/recipes/search">
                @csrf
                <button type="submit" class="search-btn">Search Recipes</button>
            </form>
        </div>

        <form method="POST" action="/recipes/add" class="inline-form">
            @csrf
            <input type="text" name="product" placeholder="Enter product">
            <button type="submit">Add</button>
        </form>

        @if(session('error'))
            <p class="error">{{ session('error') }}</p>
        @endif

        <h3>Your products:</h3>

        <ul class="product-list">
            @foreach($products as $index => $product)
                <li>
                    <span>{{ $product }}</span>

                    <form method="POST" action="/recipes/remove">
                        @csrf
                        <input type="hidden" name="index" value="{{ $index }}">
                        <button type="submit" class="danger-btn">X</button>
                    </form>
                </li>
            @endforeach
        </ul>

    </div>

</div>

</body>
</html>