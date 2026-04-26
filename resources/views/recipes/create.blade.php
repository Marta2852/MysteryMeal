<!DOCTYPE html>
<html>
<head>
    <title>Create Your Own Recipe</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .ingredient-input {
            margin-bottom: 10px;
        }
        .add-ingredient {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn {
            background-color: #666;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="/recipes/search" class="back-btn">⬅ Back</a>
    
    <h2>Create Your Own Recipe</h2>
    
    @if(session('success'))
        <div style="background-color: #d4edda; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('recipes.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label>Recipe Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label>Ingredients (one per line or use the add button):</label>
            <div id="ingredients-container">
                <div class="ingredient-input">
                    <input type="text" name="ingredients[]" placeholder="e.g., chicken" required>
                </div>
            </div>
            <button type="button" class="add-ingredient" onclick="addIngredient()">+ Add Another Ingredient</button>
        </div>
        
        <div class="form-group">
            <label>Instructions:</label>
            <textarea name="instructions" rows="10" required placeholder="Step by step instructions..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Image URL (optional):</label>
            <input type="url" name="image" placeholder="https://example.com/image.jpg">
        </div>
        
        <div class="form-group">
            <label>Prep Time (optional):</label>
            <input type="text" name="prep_time" placeholder="e.g., 15 minutes">
        </div>
        
        <div class="form-group">
            <label>Cook Time (optional):</label>
            <input type="text" name="cook_time" placeholder="e.g., 30 minutes">
        </div>
        
        <button type="submit" class="submit-btn">Create Recipe</button>
    </form>
</div>

<script>
    function addIngredient() {
        const container = document.getElementById('ingredients-container');
        const div = document.createElement('div');
        div.className = 'ingredient-input';
        div.innerHTML = '<input type="text" name="ingredients[]" placeholder="e.g., salt" required>';
        container.appendChild(div);
    }
</script>
</body>
</html>