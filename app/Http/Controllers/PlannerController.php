<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlannerController extends Controller
{
    public function index()
    {
        $interests = [
            ['id' => 'adventure', 'label' => 'Adventure', 'icon' => 'fa-hiking'],
            ['id' => 'culture', 'label' => 'Culture', 'icon' => 'fa-landmark'],
            ['id' => 'food', 'label' => 'Food & Wine', 'icon' => 'fa-utensils'],
            ['id' => 'beach', 'label' => 'Beach Relax', 'icon' => 'fa-umbrella-beach'],
            ['id' => 'nature', 'label' => 'Nature', 'icon' => 'fa-leaf'],
            ['id' => 'nightlife', 'label' => 'Nightlife', 'icon' => 'fa-music'],
            ['id' => 'history', 'label' => 'History', 'icon' => 'fa-monument'],
            ['id' => 'shopping', 'label' => 'Shopping', 'icon' => 'fa-bag-shopping'],
            ['id' => 'photography', 'label' => 'Photography', 'icon' => 'fa-camera'],
            ['id' => 'wellness', 'label' => 'Wellness', 'icon' => 'fa-spa'],
        ];

        $travelStyles = [
            ['id' => 'solo', 'label' => 'Solo', 'icon' => 'fa-user'],
            ['id' => 'couple', 'label' => 'Couple', 'icon' => 'fa-heart'],
            ['id' => 'family', 'label' => 'Family', 'icon' => 'fa-users'],
            ['id' => 'friends', 'label' => 'Friends', 'icon' => 'fa-user-group'],
        ];

        return view('planner', compact('interests', 'travelStyles'));
    }

    /**
     * Generate trip using AI (OpenAI API)
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'destination' => 'nullable|string|max:100',
            'style' => 'nullable|string|max:50',
            'days' => 'required|integer|min:1|max:30',
            'budget' => 'required|integer|min:100|max:100000',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:50',
        ]);

        $destination = $data['destination'] ?? 'Anywhere interesting';
        $style = $data['style'] ?? 'Explorer';
        $days = (int) $data['days'];
        $budget = (int) $data['budget'];
        $interests = $data['interests'] ?? ['General'];

        // Build prompt
        $prompt = $this->buildPrompt($destination, $style, $days, $budget, $interests);

        // Check if OpenAI key is configured
        $apiKey = config('services.openai.key');

        if ($apiKey && $apiKey !== 'sk-your-openai-api-key-here' && strlen($apiKey) > 20) {
            // Try OpenAI
            $aiResult = $this->callOpenAI($prompt);
            if ($aiResult) {
                return response()->json([
                    'success' => true,
                    'source' => 'ai',
                    'itinerary' => $aiResult,
                    'summary' => [
                        'destination' => $destination,
                        'days' => $days,
                        'budget' => $budget,
                        'style' => $style,
                        'interests' => $interests,
                    ],
                    'costs' => $this->calculateCosts($budget),
                ]);
            }
        }

        // Fallback: Local AI simulation
        return response()->json([
            'success' => true,
            'source' => 'local',
            'itinerary' => $this->generateLocalItinerary($destination, $style, $days, $interests),
            'summary' => [
                'destination' => $destination,
                'days' => $days,
                'budget' => $budget,
                'style' => $style,
                'interests' => $interests,
            ],
            'costs' => $this->calculateCosts($budget),
        ]);
    }

    /**
     * Build the AI prompt
     */
    private function buildPrompt($destination, $style, $days, $budget, $interests)
    {
        $interestsList = implode(', ', $interests);

        return "You are an expert travel planner. Create a detailed {$days}-day travel itinerary for {$destination}.

Travel Details:
- Traveler Style: {$style}
- Budget: \${$budget} per person
- Interests: {$interestsList}
- Duration: {$days} days

Return ONLY a valid JSON array (no markdown, no explanation) with this exact structure:
[
  {
    \"day\": 1,
    \"title\": \"Arrival & First Impressions\",
    \"morning\": \"Detailed morning activity\",
    \"afternoon\": \"Detailed afternoon activity\",
    \"evening\": \"Detailed evening activity\",
    \"tip\": \"Insider tip for the day\"
  }
]

Requirements:
- Exactly {$days} day objects
- Each activity should be specific and actionable (not generic)
- Tips should be insider/local knowledge
- Match the traveler's interests: {$interestsList}
- Respect the budget range: \${$budget}
- Return ONLY the JSON array, nothing else";
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt)
    {
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional travel planner. Always respond with valid JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 3000,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error: ' . $response->body());
                return null;
            }

            $content = $response->json('choices.0.message.content');
            if (!$content) return null;

            $decoded = json_decode($content, true);

            // Handle both direct array and {itinerary: [...]} format
            if (isset($decoded['itinerary'])) {
                return $decoded['itinerary'];
            }

            // If it's a plain array
            if (is_array($decoded) && isset($decoded[0]['day'])) {
                return $decoded;
            }

            // Try to find any array key with day objects
            foreach ($decoded as $key => $value) {
                if (is_array($value) && isset($value[0]['day'])) {
                    return $value;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fallback local itinerary generator
     */
    private function generateLocalItinerary($destination, $style, $days, $interests)
    {
        $activities = [
            'morning' => [
                'Start with a local breakfast at a recommended café',
                'Visit the main landmark and take a guided tour',
                'Explore the historic old town on foot',
                'Take a sunrise photography walk',
                'Join a local cooking class',
                'Visit the famous local market',
            ],
            'afternoon' => [
                'Try regional cuisine at a hidden gem restaurant',
                'Visit museums and cultural sites',
                'Take a scenic boat or walking tour',
                'Explore local artisan shops',
                'Relax at a nearby beach or park',
                'Join a guided adventure activity',
            ],
            'evening' => [
                'Watch the sunset from a viewpoint',
                'Enjoy dinner at a rooftop restaurant',
                'Experience local nightlife',
                'Take an evening walking tour',
                'Attend a cultural performance',
                'Relax at a spa or hammam',
            ],
            'tip' => [
                'Book tickets online to skip queues',
                'Learn a few local phrases — locals love it',
                'Carry cash for small vendors',
                'Visit popular spots early morning to avoid crowds',
                'Try street food — it\'s often the best',
                'Use public transport for authentic experience',
                'Dress modestly when visiting religious sites',
                'Keep a day free for spontaneous exploration',
            ],
        ];

        $itinerary = [];
        $interestsStr = implode(' & ', $interests);

        for ($i = 1; $i <= $days; $i++) {
            if ($i === 1) {
                $title = 'Arrival & First Impressions';
            } elseif ($i === $days) {
                $title = 'Final Day & Departure';
            } else {
                $title = 'Exploring ' . $destination . ' — Day ' . $i;
            }

            $itinerary[] = [
                'day' => $i,
                'title' => $title,
                'morning' => $activities['morning'][($i - 1) % count($activities['morning'])],
                'afternoon' => $activities['afternoon'][($i - 1) % count($activities['afternoon'])],
                'evening' => $activities['evening'][($i - 1) % count($activities['evening'])],
                'tip' => $activities['tip'][($i - 1) % count($activities['tip'])],
            ];
        }

        return $itinerary;
    }

    /**
     * Calculate cost breakdown
     */
    private function calculateCosts($budget)
    {
        return [
            'flights' => round($budget * 0.30),
            'accommodation' => round($budget * 0.35),
            'food' => round($budget * 0.20),
            'activities' => round($budget * 0.15),
            'total' => $budget,
        ];
    }
}