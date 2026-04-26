<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecipeController extends Controller
{
    public function index()
    {
        return view('recipes.search', [
            'products' => session('products', []),
            'user_recipes' => session('user_recipes', [])
        ]);
    }

    public function addProduct(Request $request)
    {
        $request->validate(['product' => 'required|string|max:255']);

        $products = session('products', []);
        $products[] = trim($request->product);

        session(['products' => array_unique($products)]);

        return redirect()->back();
    }

    public function removeProduct(Request $request)
    {
        $products = session('products', []);

        if (isset($products[$request->index])) {
            unset($products[$request->index]);
        }

        session(['products' => array_values($products)]);

        return redirect()->back();
    }

    public function search(Request $request)
    {
        $myProducts = session('products', []);

        if (empty($myProducts)) {
            return redirect()->route('recipes.index')->with('error', 'Add products first!');
        }

        $myProductsClean = array_map(fn($i) => strtolower(trim($i)), $myProducts);

        $mealList = [];
        $finalMeals = [];

        // SEARCH API RECIPES
        foreach ($myProductsClean as $ingredient) {
            $ingredient = str_replace(' ', '_', $ingredient);

            $response = Http::get(
                "https://www.themealdb.com/api/json/v1/1/filter.php?i={$ingredient}"
            );

            foreach ($response->json()['meals'] ?? [] as $meal) {
                $mealList[$meal['idMeal']] = $meal;
            }
        }

        // SEARCH USER-CREATED RECIPES FROM SESSION
        $userRecipes = session('user_recipes', []);
        
        foreach ($userRecipes as $index => $userRecipe) {
            if (!isset($userRecipe['id'])) {
                $userRecipe['id'] = uniqid();
                $userRecipes[$index] = $userRecipe;
                session(['user_recipes' => $userRecipes]);
            }
            
            $recipeIngredients = $userRecipe['ingredients'] ?? [];
            $recipeIngredientsClean = array_map(fn($i) => strtolower(trim($i)), $recipeIngredients);
            
            $hasMatchingIngredient = false;
            foreach ($myProductsClean as $userProduct) {
                foreach ($recipeIngredientsClean as $recipeIngredient) {
                    if ($this->ingredientMatches($recipeIngredient, $userProduct)) {
                        $hasMatchingIngredient = true;
                        break 2;
                    }
                }
            }
            
            if ($hasMatchingIngredient) {
                $mealList['user_' . $userRecipe['id']] = [
                    'idMeal' => 'user_' . $userRecipe['id'],
                    'strMeal' => $userRecipe['name'],
                    'strMealThumb' => $userRecipe['image'] ?? '/images/default-recipe.jpg',
                    'is_user_recipe' => true,
                    'user_recipe_data' => $userRecipe
                ];
            }
        }

        // PROCESS RESULTS
        foreach ($mealList as $item) {
            if (isset($item['is_user_recipe']) && $item['is_user_recipe']) {
                $userRecipe = $item['user_recipe_data'];
                $ingredients = array_map(fn($i) => strtolower(trim($i)), $userRecipe['ingredients'] ?? []);
                
                $have = [];
                $missing = [];
                
                foreach ($ingredients as $ing) {
                    $matched = false;
                    foreach ($myProductsClean as $userIng) {
                        if ($this->ingredientMatches($ing, $userIng)) {
                            $matched = true;
                            break;
                        }
                    }
                    
                    if ($matched) {
                        $have[] = ucfirst($ing);
                    } else {
                        $missing[] = ucfirst($ing);
                    }
                }
                
                $finalMeals[] = [
                    'idMeal' => $item['idMeal'],
                    'strMeal' => $userRecipe['name'],
                    'strMealThumb' => $userRecipe['image'] ?? '/images/default-recipe.jpg',
                    'have_ingredients' => $have,
                    'missing_ingredients' => array_slice($missing, 0, 5),
                    'all_ingredients' => $ingredients,
                    'is_user_recipe' => true,
                    'instructions' => $userRecipe['instructions'] ?? '',
                    'prep_time' => $userRecipe['prep_time'] ?? 'N/A',
                    'cook_time' => $userRecipe['cook_time'] ?? 'N/A',
                    'total_time' => $this->calculateTotalTime($userRecipe['prep_time'] ?? null, $userRecipe['cook_time'] ?? null)
                ];
                
            } else {
                $detail = Http::get(
                    "https://www.themealdb.com/api/json/v1/1/lookup.php?i={$item['idMeal']}"
                )->json()['meals'][0] ?? null;

                if (!$detail) continue;

                $ingredients = [];

                for ($i = 1; $i <= 20; $i++) {
                    $ing = trim($detail['strIngredient'.$i] ?? '');
                    if (!empty($ing)) {
                        $ingredients[] = strtolower($ing);
                    }
                }

                $have = [];
                $missing = [];
                
                foreach ($ingredients as $ing) {
                    $matched = false;
                    foreach ($myProductsClean as $userIng) {
                        if ($this->ingredientMatches($ing, $userIng)) {
                            $matched = true;
                            break;
                        }
                    }
                    
                    if ($matched) {
                        $have[] = ucfirst($ing);
                    } else {
                        $missing[] = ucfirst($ing);
                    }
                }

                $times = $this->generateCookingTime($detail['strMeal'], $detail['strCategory'] ?? '');
                
                $finalMeals[] = [
                    'idMeal' => $detail['idMeal'],
                    'strMeal' => $detail['strMeal'],
                    'strMealThumb' => $detail['strMealThumb'],
                    'have_ingredients' => $have,
                    'missing_ingredients' => array_slice($missing, 0, 5),
                    'all_ingredients' => $ingredients,
                    'is_user_recipe' => false,
                    'instructions' => $detail['strInstructions'] ?? '',
                    'strCategory' => $detail['strCategory'] ?? '',
                    'strArea' => $detail['strArea'] ?? '',
                    'prep_time' => $times['prep'],
                    'cook_time' => $times['cook'],
                    'total_time' => $times['total']
                ];
            }
        }

        // APPLY TIME FILTER
        $maxTime = $request->input('max_time');
        if ($maxTime && is_numeric($maxTime)) {
            $finalMeals = array_filter($finalMeals, function($meal) use ($maxTime) {
                $totalTime = $meal['total_time'];
                if ($totalTime === 'N/A') return true;
                preg_match('/(\d+)/', $totalTime, $matches);
                $timeValue = $matches[1] ?? 0;
                return $timeValue <= $maxTime;
            });
            $finalMeals = array_values($finalMeals);
        }

        $finalMeals = array_slice($finalMeals, 0, 10);
        
        // STORE RESULTS IN SESSION
        session(['last_results' => $finalMeals]);
        session(['last_products' => $myProducts]);
        session(['last_max_time' => $maxTime ?? null]);

        return view('recipes.results', [
            'meals' => $finalMeals,
            'products' => $myProducts,
            'selectedMaxTime' => $maxTime ?? null
        ]);
    }
    
    // HELPER METHOD FOR INGREDIENT MATCHING
   private function ingredientMatches($recipeIngredient, $userProduct)
{
    $recipeIngredient = strtolower(trim($recipeIngredient));
    $userProduct = strtolower(trim($userProduct));

    // 1. EXACT MATCH ONLY (fixes beef stock issue)
    if ($recipeIngredient === $userProduct) {
        return true;
    }

    // 2. Plural handling (basic)
    if ($userProduct . 's' === $recipeIngredient || $userProduct . 'es' === $recipeIngredient) {
        return true;
    }

    // 3. Allowed variations (safe expansions only)
    $allowed = [
        'beef' => ['ground beef', 'beef steak', 'minced beef', 'beef chunk'],
        'chicken' => ['chicken breast', 'chicken thigh', 'chicken leg', 'whole chicken'],
        'pork' => ['pork chop', 'pork belly', 'ground pork'],
        'fish' => ['fish fillet', 'whole fish'],
        'tomato' => ['tomatoes', 'cherry tomato'],
        'onion' => ['onions', 'red onion', 'white onion', 'yellow onion'],
        'garlic' => ['garlic clove', 'garlic powder'],
        'potato' => ['potatoes', 'sweet potato'],
        'carrot' => ['carrots'],
        'celery' => ['celery stalk'],
        'milk' => ['whole milk', 'skim milk', 'coconut milk'],
        'cheese' => ['cheddar cheese', 'parmesan cheese', 'cream cheese'],
        'rice' => ['white rice', 'brown rice', 'wild rice'],
        'pasta' => ['spaghetti', 'fettuccine', 'macaroni'],
        'flour' => ['all-purpose flour', 'wheat flour'],
        'sugar' => ['white sugar', 'brown sugar', 'powdered sugar'],
        'egg' => ['eggs', 'egg yolk', 'egg white'],
        'butter' => ['unsalted butter', 'salted butter'],
        'oil' => ['olive oil', 'vegetable oil', 'coconut oil'],
    ];

    if (isset($allowed[$userProduct])) {
        return in_array($recipeIngredient, $allowed[$userProduct]);
    }

    return false;
}
    
    public function showResults(Request $request)
    {
        // Retrieve results from session
        $meals = session('last_results', []);
        $products = session('last_products', []);
        $selectedMaxTime = session('last_max_time', null);
        
        if (empty($meals)) {
            return redirect()->route('recipes.index')->with('error', 'No search results found. Please search again.');
        }
        
        return view('recipes.results', [
            'meals' => $meals,
            'products' => $products,
            'selectedMaxTime' => $selectedMaxTime
        ]);
    }

    private function generateCookingTime($recipeName, $category)
    {
        $times = [
            'Breakfast' => ['prep' => 10, 'cook' => 15],
            'Starter' => ['prep' => 15, 'cook' => 20],
            'Appetizer' => ['prep' => 15, 'cook' => 20],
            'Salad' => ['prep' => 15, 'cook' => 5],
            'Soup' => ['prep' => 15, 'cook' => 30],
            'Main Course' => ['prep' => 20, 'cook' => 40],
            'Side' => ['prep' => 10, 'cook' => 20],
            'Dessert' => ['prep' => 20, 'cook' => 30],
            'Snack' => ['prep' => 10, 'cook' => 10],
            'Drink' => ['prep' => 5, 'cook' => 0],
            'Sauce' => ['prep' => 10, 'cook' => 15],
            'Pork' => ['prep' => 20, 'cook' => 45],
            'Beef' => ['prep' => 20, 'cook' => 50],
            'Chicken' => ['prep' => 15, 'cook' => 35],
            'Seafood' => ['prep' => 15, 'cook' => 20],
            'Vegetarian' => ['prep' => 15, 'cook' => 25],
            'Vegan' => ['prep' => 15, 'cook' => 25],
            'Pasta' => ['prep' => 10, 'cook' => 20],
            'Rice' => ['prep' => 10, 'cook' => 25],
            'Curry' => ['prep' => 15, 'cook' => 35],
            'Stew' => ['prep' => 20, 'cook' => 60],
            'Casserole' => ['prep' => 20, 'cook' => 55],
            'Baking' => ['prep' => 20, 'cook' => 40],
            'Grill' => ['prep' => 15, 'cook' => 25],
            'Roast' => ['prep' => 20, 'cook' => 60],
            'Stir-fry' => ['prep' => 15, 'cook' => 15],
        ];

        $prep = 15;
        $cook = 30;

        if (isset($times[$category])) {
            $prep = $times[$category]['prep'];
            $cook = $times[$category]['cook'];
        }
        
        $recipeNameLower = strtolower($recipeName);
        foreach ($times as $key => $time) {
            if (str_contains($recipeNameLower, strtolower($key))) {
                $prep = $time['prep'];
                $cook = $time['cook'];
                break;
            }
        }

        $prep = max(5, $prep + rand(-5, 5));
        $cook = max(0, $cook + rand(-10, 10));
        
        $prepTime = $prep > 0 ? $prep . ' minutes' : 'N/A';
        $cookTime = $cook > 0 ? $cook . ' minutes' : 'N/A';
        $totalTime = ($prep + $cook) . ' minutes';

        return [
            'prep' => $prepTime,
            'cook' => $cookTime,
            'total' => $totalTime
        ];
    }

    private function calculateTotalTime($prepTime, $cookTime)
    {
        $total = 0;
        
        if ($prepTime && $prepTime !== 'N/A') {
            preg_match('/(\d+)/', $prepTime, $prepMatches);
            $total += intval($prepMatches[1] ?? 0);
        }
        
        if ($cookTime && $cookTime !== 'N/A') {
            preg_match('/(\d+)/', $cookTime, $cookMatches);
            $total += intval($cookMatches[1] ?? 0);
        }
        
        return $total > 0 ? $total . ' minutes' : 'N/A';
    }

    public function addFavorite(Request $request)
    {
        $favorites = session('favorites', []);
        
        foreach ($favorites as $fav) {
            if ($fav['id'] == $request->meal_id) {
                return redirect()->back()->with('error', 'Recipe already in favorites!');
            }
        }
        
        if (strpos($request->meal_id, 'user_') === 0) {
            $userRecipes = session('user_recipes', []);
            $userRecipeId = str_replace('user_', '', $request->meal_id);
            
            $userRecipe = null;
            foreach ($userRecipes as $recipe) {
                if ($recipe['id'] == $userRecipeId) {
                    $userRecipe = $recipe;
                    break;
                }
            }
            
            if ($userRecipe) {
                $allIngredients = array_map(fn($i) => strtolower(trim($i)), $userRecipe['ingredients'] ?? []);
                
                $favorite = [
                    'id' => $request->meal_id,
                    'name' => $userRecipe['name'],
                    'thumb' => $userRecipe['image'] ?? '/images/default-recipe.jpg',
                    'all_ingredients' => $allIngredients,
                    'have_ingredients' => [],
                    'missing_ingredients' => [],
                    'is_user_recipe' => true,
                    'instructions' => $userRecipe['instructions'] ?? ''
                ];
                
                $favorites[] = $favorite;
                session(['favorites' => $favorites]);
                
                return redirect()->back()->with('success', 'Your recipe added to favorites!');
            }
        }
        
        $response = Http::get(
            "https://www.themealdb.com/api/json/v1/1/lookup.php?i={$request->meal_id}"
        );
        
        $meal = $response->json()['meals'][0] ?? null;
        
        if (!$meal) {
            return redirect()->back()->with('error', 'Recipe not found!');
        }
        
        $allIngredients = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $ingredient = trim($meal['strIngredient' . $i] ?? '');
            
            if (!empty($ingredient)) {
                $allIngredients[] = strtolower($ingredient);
            }
        }
        
        $favorite = [
            'id' => $meal['idMeal'],
            'name' => $meal['strMeal'],
            'thumb' => $meal['strMealThumb'],
            'all_ingredients' => $allIngredients,
            'have_ingredients' => [],
            'missing_ingredients' => [],
            'is_user_recipe' => false
        ];
        
        $favorites[] = $favorite;
        session(['favorites' => $favorites]);
        
        return redirect()->back()->with('success', 'Recipe added to favorites!');
    }
    
    public function favorites()
    {
        $favorites = session('favorites', []);
        $currentProducts = session('products', []);
        
        $currentProductsClean = array_map(function($item) {
            return strtolower(trim($item));
        }, $currentProducts);
        
        foreach ($favorites as $key => &$favorite) {
            $have = [];
            $missing = [];
            
            if (isset($favorite['all_ingredients']) && !empty($favorite['all_ingredients'])) {
                
                foreach ($favorite['all_ingredients'] as $recipeIngredient) {
                    $matched = false;
                    
                    foreach ($currentProductsClean as $userProduct) {
                        if ($this->ingredientMatches($recipeIngredient, $userProduct)) {
                            $matched = true;
                            break;
                        }
                    }
                    
                    if ($matched) {
                        $have[] = ucfirst($recipeIngredient);
                    } else {
                        $missing[] = ucfirst($recipeIngredient);
                    }
                }
            }
            
            $favorite['have_ingredients'] = $have;
            $favorite['missing_ingredients'] = array_slice($missing, 0, 10);
        }
        
        return view('recipes.favorites', [
            'favorites' => $favorites
        ]);
    }
    
    public function removeFavorite(Request $request)
    {
        $favorites = session('favorites', []);
        
        foreach ($favorites as $key => $fav) {
            if ($fav['id'] == $request->meal_id) {
                unset($favorites[$key]);
                break;
            }
        }
        
        session(['favorites' => array_values($favorites)]);
        
        return redirect()->back()->with('success', 'Recipe removed from favorites!');
    }

    public function create()
    {
        return view('recipes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'required|string',
            'instructions' => 'required|string',
            'image' => 'nullable|url'
        ]);
        
        $userRecipes = session('user_recipes', []);
        
        $newRecipe = [
            'id' => uniqid(),
            'name' => $request->name,
            'ingredients' => $request->ingredients,
            'instructions' => $request->instructions,
            'image' => $request->image ?? null,
            'prep_time' => $request->prep_time ?? null,
            'cook_time' => $request->cook_time ?? null,
            'created_at' => now()
        ];
        
        $userRecipes[] = $newRecipe;
        session(['user_recipes' => $userRecipes]);
        
        return redirect()->route('recipes.index')->with('success', 'Your recipe has been created!');
    }

    public function filter(Request $request)
    {
        return redirect()->route('recipes.search');
    }
}