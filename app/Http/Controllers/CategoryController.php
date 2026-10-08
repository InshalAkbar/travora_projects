<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ExploreController;

class CategoryController extends Controller
{
    public function show($category)
    {
        $allDestinations = ExploreController::getDestinations();

        // Category ke saare destinations
        $destinations = collect($allDestinations)
            ->where('category', $category)
            ->values()
            ->all();

        if (count($destinations) === 0) {
            abort(404, 'Category not found');
        }

        // Category info (icon, image)
        $categoryInfo = $this->getCategoryInfo($category);

        return view('category-explore', compact('destinations', 'category', 'categoryInfo'));
    }

    private function getCategoryInfo($category)
    {
        $info = [
            'Beaches' => [
                'icon' => 'fa-umbrella-beach',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Sun, sand, and crystal-clear waters. Discover the world\'s most beautiful coastlines and island escapes.',
            ],
            'Mountains' => [
                'icon' => 'fa-mountain',
                'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Breathtaking peaks, alpine villages, and adventure awaits. Find your perfect mountain escape.',
            ],
            'Cities' => [
                'icon' => 'fa-city',
                'image' => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Vibrant metropolises full of culture, food, and nightlife. Experience the pulse of the world\'s greatest cities.',
            ],
            'Deserts' => [
                'icon' => 'fa-sun',
                'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Golden dunes, starry nights, and ancient cultures. Explore the serene beauty of the desert.',
            ],
            'Forests' => [
                'icon' => 'fa-tree',
                'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Lush greenery, ancient trees, and peaceful trails. Reconnect with nature in the world\'s most beautiful forests.',
            ],
            'Islands' => [
                'icon' => 'fa-water',
                'image' => 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?q=80&w=2000&auto=format&fit=crop',
                'description' => 'Tropical paradises, overwater villas, and endless blue. Find your perfect island getaway.',
            ],
        ];

        return $info[$category] ?? [
            'icon' => 'fa-globe',
            'image' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?q=80&w=2000&auto=format&fit=crop',
            'description' => 'Explore amazing destinations around the world.',
        ];
    }
}