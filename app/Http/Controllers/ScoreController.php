<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Score::all();
    }

    public function gameover()
    {
        $user = Auth::user();
        $highScore = Score::where('user_id', $user->id)->max('score') ?? 0;
        $longestTime = Score::where('user_id', $user->id)->max('time') ?? 0;
        
        return view('minigame/gameover', [
            'highScore' => $highScore,
            'longestTime' => $longestTime
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'score' => 'required|integer',
            'time' => 'required|integer',
        ]);

        Score::create($validated);

        return response()->json([
            'success' => true,
            'score' => $validated['score'],
            'time' => $validated['time']
        ]);
    }
}
