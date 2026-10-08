<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ExploreController;

class HomeController extends Controller
{
    public function index()
    {
        // Get all destinations from shared source
        $allDestinations = ExploreController::getDestinations();

        // Take first 3 as featured
        $featuredDestinations = array_slice($allDestinations, 0, 3);

        // ===== CATEGORIES =====
        $categories = [
            [
                'name' => 'Beaches',
                'icon' => 'fa-umbrella-beach',
                'count' => '124 destinations',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'name' => 'Mountains',
                'icon' => 'fa-mountain',
                'count' => '87 destinations',
                'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'name' => 'Cities',
                'icon' => 'fa-city',
                'count' => '203 destinations',
                'image' => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'name' => 'Deserts',
                'icon' => 'fa-sun',
                'count' => '42 destinations',
                'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'name' => 'Forests',
                'icon' => 'fa-tree',
                'count' => '65 destinations',
                'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'name' => 'Islands',
                'icon' => 'fa-water',
                'count' => '98 destinations',
                'image' => 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?q=80&w=800&auto=format&fit=crop'
            ],
        ];

        // ===== FEATURES =====
        $features = [
            [
                'icon' => 'fa-wand-magic-sparkles',
                'title' => 'AI Trip Planner',
                'desc' => 'Let our intelligent AI craft a personalized itinerary based on your interests, budget, and time.'
            ],
            [
                'icon' => 'fa-map-location-dot',
                'title' => 'Curated Destinations',
                'desc' => 'Handpicked locations from around the world that go beyond the ordinary tourist trail.'
            ],
            [
                'icon' => 'fa-wallet',
                'title' => 'Smart Budgeting',
                'desc' => 'Real-time cost estimates, currency conversion, and budget tracking for every trip.'
            ],
            [
                'icon' => 'fa-shield-heart',
                'title' => 'Trusted Guides',
                'desc' => 'Verified local guides and insider tips to make your journey safe and unforgettable.'
            ],
        ];

        // ===== TESTIMONIALS =====
        $testimonials = [
            [
                'name' => 'Sarah Mitchell',
                'location' => 'London, UK',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'text' => 'Travora completely changed how I plan my trips. The AI planner suggested hidden gems in Japan I never would have found on my own.'
            ],
            [
                'name' => 'Ahmed Khan',
                'location' => 'Dubai, UAE',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'text' => 'The budget calculator is a game changer. I planned a 10-day Europe trip and stayed 20% under my budget!'
            ],
            [
                'name' => 'Yuki Tanaka',
                'location' => 'Tokyo, Japan',
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'text' => 'Beautiful interface, but the real magic is in how personal every recommendation feels. Highly recommended!'
            ],
        ];

        return view('home', compact(
            'featuredDestinations',
            'categories',
            'features',
            'testimonials'
        ));
    }
}