<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuidesController extends Controller
{
    public function index()
    {
        $guides = [
            [
                'id' => 1,
                'title' => 'Ultimate Guide to Amalfi Coast',
                'excerpt' => 'Everything you need to know about Italy\'s most iconic coastline — from hidden beaches to cliffside restaurants.',
                'category' => 'Europe',
                'read_time' => '12 min read',
                'author' => 'Sarah Mitchell',
                'author_avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=200&auto=format&fit=crop',
                'date' => 'Mar 15, 2026',
                'image' => 'https://images.unsplash.com/photo-1533105079780-92b9be482077?q=80&w=1000&auto=format&fit=crop',
                'featured' => true
            ],
            [
                'id' => 2,
                'title' => 'Chasing Northern Lights in Iceland',
                'excerpt' => 'A complete guide to witnessing the aurora borealis — best months, locations, and photography tips.',
                'category' => 'Nordic',
                'read_time' => '9 min read',
                'author' => 'Ahmed Khan',
                'author_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
                'date' => 'Mar 10, 2026',
                'image' => 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?q=80&w=1000&auto=format&fit=crop',
                'featured' => false
            ],
            [
                'id' => 3,
                'title' => 'Kyoto\'s Hidden Temples & Tea Houses',
                'excerpt' => 'Skip the crowds and discover the quiet side of Japan\'s ancient capital through local secrets.',
                'category' => 'Asia',
                'read_time' => '15 min read',
                'author' => 'Yuki Tanaka',
                'author_avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=200&auto=format&fit=crop',
                'date' => 'Mar 5, 2026',
                'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=1000&auto=format&fit=crop',
                'featured' => false
            ],
            [
                'id' => 4,
                'title' => 'Sahara Desert: A Night Under the Stars',
                'excerpt' => 'What it\'s really like to camp in the world\'s largest hot desert — practical tips and unforgettable moments.',
                'category' => 'Africa',
                'read_time' => '8 min read',
                'author' => 'Sarah Mitchell',
                'author_avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=200&auto=format&fit=crop',
                'date' => 'Feb 28, 2026',
                'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?q=80&w=1000&auto=format&fit=crop',
                'featured' => false
            ],
            [
                'id' => 5,
                'title' => 'Maldives on a Budget: Yes, It\'s Possible',
                'excerpt' => 'How to experience paradise without emptying your savings — local islands, guesthouses, and smart planning.',
                'category' => 'Indian Ocean',
                'read_time' => '10 min read',
                'author' => 'Ahmed Khan',
                'author_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
                'date' => 'Feb 20, 2026',
                'image' => 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?q=80&w=1000&auto=format&fit=crop',
                'featured' => false
            ],
            [
                'id' => 6,
                'title' => 'Trekking to Machu Picchu: All You Need',
                'excerpt' => 'Inca Trail vs. alternative routes, altitude prep, and what to pack for the adventure of a lifetime.',
                'category' => 'South America',
                'read_time' => '14 min read',
                'author' => 'Yuki Tanaka',
                'author_avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=200&auto=format&fit=crop',
                'date' => 'Feb 15, 2026',
                'image' => 'https://images.unsplash.com/photo-1526392060635-9d6019884377?q=80&w=1000&auto=format&fit=crop',
                'featured' => false
            ],
        ];

        $categories = ['All', 'Europe', 'Asia', 'Africa', 'Nordic', 'Indian Ocean', 'South America'];

        return view('guides', compact('guides', 'categories'));
    }
}