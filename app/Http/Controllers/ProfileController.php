<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\UserActivity;
use App\Http\Controllers\ExploreController;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // User ki bookings
        $bookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // User ke favorites (session-based)
        $favoriteIds = session()->get('favorites', []);
        $allDestinations = ExploreController::getDestinations();
        $favorites = collect($allDestinations)
            ->whereIn('id', $favoriteIds)
            ->values()
            ->all();

        // User activities
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('profile', compact('user', 'bookings', 'favorites', 'activities'));
    }
}