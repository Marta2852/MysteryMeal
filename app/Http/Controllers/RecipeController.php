<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecipeController extends Controller
{
    public function index()
    {
        $products = session('products', []);
        return view('recipes.search', compact('products'));
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'product' => 'required|string|max:255'
        ]);

        $products = session('products', []);
        $products[] = $request->product;

        session(['products' => $products]);

        return redirect('/recipes/search');
    }

    public function removeProduct(Request $request)
    {
        $index = $request->index;
        $products = session('products', []);

        if (isset($products[$index])) {
            unset($products[$index]);
        }

        session(['products' => array_values($products)]);

        return redirect('/recipes/search');
    }

    /**
     * STRICT ingredient matcher
     */
    private function matchesIngredient($userInput, $recipeIngredient)
    {
        $userInput = strtolower(trim($userInput));
        $recipeIngredient = strtolower(trim($recipeIngredient));

        // ❌ block unwanted matches like "chicken stock"
        $blockedWords = ['stock', 'broth', 'sauce', 'gravy'];

        foreach ($blockedWords as $word) {
            if (str_contains($recipeIngredient, $word)) {
                return false;
            }
        }

        // ✅ exact match
        if ($userInput === $recipeIngredient) {
            return true;
        }

        // ✅ allow "chicken breast", "chicken fillet"
        if (str_starts_with($recipeIngredient, $userInput . ' ')) {
            return true;
        }

        return false;
    }

    public function search()
    {
        $myProducts = session('products', []);

        if (count($myProducts) === 0) {
            return back()->with('error', 'Pievieno vismaz vienu produktu!');
        }

        // normalize input
        $myProductsClean = array_map(function($item) {
            return strtolower(trim($item));
        }, $myProducts);

        $firstItem = str_replace(' ', '_', $myProductsClean[0]);

        $response = Http::get("https://www.themealdb.com/api/json/v1/1/filter.php?i=$firstItem");
        $mealList = $response->json()['meals'];

        if (!$mealList) {
            return back()->with('error', "Nekas netika atrasts ar ingredientu: " . $myProducts[0]);
        }

        $finalMeals = [];

        foreach (array_slice($mealList, 0, 10) as $item) {
            $detailResponse = Http::get("https://www.themealdb.com/api/json/v1/1/lookup.php?i={$item['idMeal']}");
            $mealDetail = $detailResponse->json()['meals'][0];

            $recipeIngredients = [];
            $missingIngredients = [];
            $haveIngredients = [];

            // collect ingredients
            for ($i = 1; $i <= 20; $i++) {
                $ingName = $mealDetail['strIngredient' . $i];

                if (!empty(trim($ingName))) {
                    $recipeIngredients[] = strtolower(trim($ingName));
                }
            }

            // ✅ check ALL user ingredients exist
            $matchedUserIngredients = [];

            foreach ($myProductsClean as $myProd) {
                foreach ($recipeIngredients as $recipeIng) {
                    if ($this->matchesIngredient($myProd, $recipeIng)) {
                        $matchedUserIngredients[] = $myProd;
                        break;
                    }
                }
            }

            // ❌ skip if not ALL matched
            if (count($matchedUserIngredients) !== count($myProductsClean)) {
                continue;
            }

            // build have / missing lists
            for ($i = 1; $i <= 20; $i++) {
                $ingName = $mealDetail['strIngredient' . $i];
                $measure = $mealDetail['strMeasure' . $i];

                if (!empty(trim($ingName))) {
                    $ingNameLower = strtolower(trim($ingName));
                    $fullIngredient = $ingName . " (" . $measure . ")";

                    $found = false;

                    foreach ($myProductsClean as $myProd) {
                        if ($this->matchesIngredient($myProd, $ingNameLower)) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        $haveIngredients[] = $fullIngredient;
                    } else {
                        $missingIngredients[] = $fullIngredient;
                    }
                }
            }

            $mealDetail['have_ingredients'] = $haveIngredients;
            $mealDetail['missing_ingredients'] = array_slice($missingIngredients, 0, 5);
            $finalMeals[] = $mealDetail;
        }

        if (empty($finalMeals)) {
            return back()->with('error', 'Nav nevienas receptes ar visiem šiem produktiem!');
        }

        return view('recipes.results', [
            'meals' => $finalMeals,
            'products' => $myProducts
        ]);
    }
}