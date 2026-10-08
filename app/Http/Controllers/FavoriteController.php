<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ExploreController;

class FavoriteController extends Controller
{
    /**
     * Show all favorite destinations
     */
    public function index()
    {
        $favoriteIds = session()->get('favorites', []);
        $allDestinations = ExploreController::getDestinations();
        
        $favorites = collect($allDestinations)
            ->whereIn('id', $favoriteIds)
            ->values()
            ->all();

        return view('favorites', compact('favorites'));
    }

    /**
     * Toggle favorite status (AJAX)
     */
    public function toggle(Request $request)
    {
        $id = (int) $request->input('id');
        $favorites = session()->get('favorites', []);

        if (in_array($id, $favorites)) {
            // Remove
            $favorites = array_values(array_diff($favorites, [$id]));
            $action = 'removed';
        } else {
            // Add
            $favorites[] = $id;
            $action = 'added';
        }

        session()->put('favorites', $favorites);

        return response()->json([
            'success' => true,
            'action' => $action,
            'count' => count($favorites),
            'id' => $id,
        ]);
    }

    /**
     * Clear all favorites
     */
    public function clear()
    {
        session()->forget('favorites');
        
        return response()->json([
            'success' => true,
            'message' => 'All favorites cleared',
        ]);
    }
}