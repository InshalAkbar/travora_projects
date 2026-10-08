<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiConversation;
use App\Http\Controllers\ExploreController;

class AiAssistantController extends Controller
{
    public function index()
    {
        // User ki previous conversations (agar logged in hai)
        $conversations = [];
        if (auth()->check()) {
            $conversations = AiConversation::where('user_id', auth()->id())
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();
        }

        return view('ai-assistant', compact('conversations'));
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = trim($request->input('message'));
        $aiResponse = $this->generateResponse($userMessage);

        // Save conversation
        $conversation = AiConversation::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'user_message' => $userMessage,
            'ai_response' => $aiResponse['text'],
            'intent' => $aiResponse['intent'],
        ]);

        return response()->json([
            'success' => true,
            'response' => $aiResponse['text'],
            'intent' => $aiResponse['intent'],
            'cards' => $aiResponse['cards'] ?? [],
            'conversation_id' => $conversation->id,
        ]);
    }

    /**
     * Local rule-based AI response generator
     */
    private function generateResponse($message)
    {
        $msg = strtolower($message);

        // ===== DESTINATION SEARCH =====
        $destinations = ExploreController::getDestinations();

        // Check if user mentioned any destination
        foreach ($destinations as $dest) {
            $title = strtolower($dest['title']);
            $country = strtolower($dest['country']);
            $region = strtolower($dest['region']);

            if (str_contains($msg, $title) || str_contains($msg, $country) || str_contains($msg, $region)) {
                return $this->destinationResponse($dest, $message);
            }
        }

        // ===== GREETING =====
        if (preg_match('/\b(hi|hello|hey|salam|assalam|hola)\b/', $msg)) {
            return [
                'intent' => 'greeting',
                'text' => "Hello! 👋 I'm Travora AI, your personal travel concierge. I can help you:\n\n✈️ **Plan a trip** — Tell me a destination and I'll create an itinerary\n💰 **Budget your journey** — Get a cost breakdown\n🌍 **Discover places** — Ask me for recommendations\n🧳 **Packing lists** — Get a custom packing list\n🍜 **Food recommendations** — Find local cuisine\n\nWhat would you like to explore today?",
            ];
        }

        // ===== BUDGET =====
        if (preg_match('/\b(budget|cost|price|money|expensive|cheap|afford)\b/', $msg)) {
            // Extract number if present
            preg_match('/\$?(\d{3,6})/', $msg, $matches);
            $budget = $matches[1] ?? 2000;

            return [
                'intent' => 'budget',
                'text' => "Here's a suggested budget breakdown for a **\$$budget** trip:\n\n" .
                    "✈️ **Flights:** \$" . round($budget * 0.30) . "\n" .
                    "🏨 **Accommodation:** \$" . round($budget * 0.35) . "\n" .
                    "🍽️ **Food:** \$" . round($budget * 0.20) . "\n" .
                    "🎯 **Activities:** \$" . round($budget * 0.15) . "\n\n" .
                    "**Total: \$" . $budget . "**\n\n" .
                    "💡 *Tip: Book flights 6-8 weeks in advance for the best deals. Travel during shoulder season (April-May or September-October) to save 20-30%.*",
            ];
        }

        // ===== PACKING LIST =====
        if (preg_match('/\b(pack|packing|bring|carry|suitcase|bag)\b/', $msg)) {
            return [
                'intent' => 'packing',
                'text' => "Here's a **smart packing list** for your trip:\n\n" .
                    "📄 **Essentials**\n☑ Passport & visa\n☑ Travel insurance\n☑ Boarding passes\n☑ Credit cards & cash\n\n" .
                    "👕 **Clothing**\n☑ 5-7 T-shirts\n☑ 2 pairs of pants/jeans\n☑ 1 jacket (weather-dependent)\n☑ Comfortable walking shoes\n☑ Sleepwear\n\n" .
                    "🔌 **Electronics**\n☑ Phone + charger\n☑ Power adapter\n☑ Power bank\n☑ Camera\n☑ Headphones\n\n" .
                    "💊 **Health**\n☑ Prescription meds\n☑ Pain relievers\n☑ Band-aids\n☑ Hand sanitizer\n\n" .
                    "💡 *Tip: Roll your clothes instead of folding — saves 30% space!*",
            ];
        }

        // ===== FOOD =====
        if (preg_match('/\b(food|eat|restaurant|cuisine|dish|breakfast|lunch|dinner|hungry)\b/', $msg)) {
            return [
                'intent' => 'food',
                'text' => "🍜 **Food Recommendations**\n\nWhen traveling, I always recommend:\n\n" .
                    "🌅 **Breakfast:** Find a local café away from tourist areas — usually 50% cheaper and 10x better\n" .
                    "🌞 **Lunch:** Street food is your friend! Look for busy stalls with locals\n" .
                    "🌙 **Dinner:** Ask your hotel/hostel staff for their favorite local spot\n\n" .
                    "**Must-try experiences:**\n☑ Local market tour\n☑ Cooking class (2-3 hours)\n☑ Food walking tour\n☑ Family-run restaurant\n\n" .
                    "💡 *Tell me a specific destination and I'll give you personalized food recommendations!*",
            ];
        }

        // ===== ITINERARY / PLAN =====
        if (preg_match('/\b(plan|itinerary|schedule|days?|trip|journey)\b/', $msg)) {
            preg_match('/(\d+)\s*days?/', $msg, $matches);
            $days = $matches[1] ?? 5;

            return [
                'intent' => 'itinerary',
                'text' => "I'd love to plan a **$days-day trip** for you! 🌍\n\nTo create the perfect itinerary, tell me:\n\n" .
                    "1️⃣ **Where** do you want to go? (e.g., Turkey, Japan, Italy)\n" .
                    "2️⃣ **What's your budget?** (e.g., \$2000)\n" .
                    "3️⃣ **Travel style?** (Solo / Couple / Family / Friends)\n" .
                    "4️⃣ **Interests?** (Adventure / Culture / Food / Relaxation)\n\n" .
                    "💡 *Or just say: \"Plan 7 days in Turkey with \$2500 budget\" and I'll create it instantly!*\n\n" .
                    "For a detailed AI-powered itinerary, try our [AI Trip Planner](/planner) →",
            ];
        }

        // ===== WEATHER =====
        if (preg_match('/\b(weather|rain|sunny|cold|hot|temperature|season)\b/', $msg)) {
            return [
                'intent' => 'weather',
                'text' => "🌤️ **Weather-Aware Travel Tips**\n\nBefore you go, always check:\n\n" .
                    "📱 **Apps:** Weather.com, AccuWeather, or Windy\n" .
                    "🌡️ **Best seasons:**\n" .
                    "• Europe: April-June & September-October\n" .
                    "• Asia: November-February (dry season)\n" .
                    "• Tropics: Avoid monsoon months\n\n" .
                    "💡 *Pro tip: Travel during shoulder season — fewer crowds, lower prices, and still great weather!*\n\n" .
                    "Tell me your destination and I'll suggest the best time to visit.",
            ];
        }

        // ===== HOTEL =====
        if (preg_match('/\b(hotel|hostel|stay|accommodation|where to stay|sleep)\b/', $msg)) {
            return [
                'intent' => 'hotel',
                'text' => "🏨 **Where to Stay — Smart Tips**\n\nInstead of picking a random hotel, choose an **area** first:\n\n" .
                    "📍 **Near attractions** — Convenient, but pricier\n" .
                    "📍 **Local neighborhoods** — Authentic, cheaper, better food\n" .
                    "📍 **Near transit** — Best for exploring multiple areas\n\n" .
                    "**Booking tips:**\n☑ Book 6-8 weeks ahead\n☑ Read recent reviews (last 3 months)\n☑ Check cancellation policy\n☑ Compare on Booking.com, Agoda, and direct hotel sites\n\n" .
                    "💡 *Tell me a destination and I'll recommend the best areas to stay!*",
            ];
        }

        // ===== FLIGHTS =====
        if (preg_match('/\b(flight|fly|airline|airport|plane)\b/', $msg)) {
            return [
                'intent' => 'flights',
                'text' => "✈️ **Flight Booking Tips**\n\n" .
                    "📅 **Best time to book:** 6-8 weeks before departure\n" .
                    "📆 **Cheapest days to fly:** Tuesday, Wednesday, Saturday\n" .
                    "⏰ **Best time to search:** Tuesday mornings\n\n" .
                    "**Tools to use:**\n☑ Google Flights (best for flexibility)\n☑ Skyscanner (best for deals)\n☑ Kayak (best for price alerts)\n☑ Momondo (best for hidden fares)\n\n" .
                    "💡 *Pro tip: Set price alerts and be flexible with dates — can save you 40%!*",
            ];
        }

        // ===== DEFAULT / HELP =====
        return [
            'intent' => 'general',
            'text' => "I'm here to help with your travel planning! 🌍\n\nHere's what I can do:\n\n" .
                "✈️ **\"Plan 7 days in Turkey\"** — Get a full itinerary\n" .
                "💰 **\"Budget for Japan trip\"** — Get cost breakdown\n" .
                "🧳 **\"What to pack for Iceland\"** — Packing list\n" .
                "🍜 **\"Best food in Italy\"** — Food recommendations\n" .
                "🏨 **\"Where to stay in Paris\"** — Hotel area tips\n" .
                "🌤️ **\"Best time to visit Bali\"** — Weather info\n" .
                "🌍 **\"Recommend a beach destination\"** — Get suggestions\n\n" .
                "Try asking me anything about travel! For a full AI itinerary, visit our [Trip Planner](/planner).",
        ];
    }

    /**
     * Destination-specific response
     */
    private function destinationResponse($dest, $originalMessage)
    {
        $msg = strtolower($originalMessage);

        // Check if user wants itinerary
        if (preg_match('/\b(plan|itinerary|days?|schedule)\b/', $msg)) {
            preg_match('/(\d+)\s*days?/', $msg, $matches);
            $days = $matches[1] ?? 5;

            $text = "🌍 **{$days}-Day Itinerary for {$dest['title']}, {$dest['country']}**\n\n";
            $text .= "📍 **Region:** {$dest['region']}\n";
            $text .= "⭐ **Rating:** {$dest['rating']}/5\n";
            $text .= "💰 **Starting from:** {$dest['price']} per person\n";
            $text .= "📅 **Best time:** {$dest['best_time']}\n\n";

            $text .= "**Highlights to experience:**\n";
            foreach ($dest['highlights'] as $h) {
                $text .= "☑ $h\n";
            }

            $text .= "\n💡 *For a detailed day-by-day itinerary, visit our [AI Trip Planner](/planner) and enter \"{$dest['title']}\".*";

            return [
                'intent' => 'destination_itinerary',
                'text' => $text,
                'cards' => [$dest],
            ];
        }

        // General destination info
        $text = "🌟 **{$dest['title']}, {$dest['country']}**\n\n";
        $text .= "{$dest['description']}\n\n";
        $text .= "**Quick Facts:**\n";
        $text .= "📅 **Best time:** {$dest['best_time']}\n";
        $text .= "🗣️ **Language:** {$dest['language']}\n";
        $text .= "💵 **Currency:** {$dest['currency']}\n";
        $text .= "⭐ **Rating:** {$dest['rating']}/5\n";
        $text .= "💰 **From:** {$dest['price']} per person\n\n";
        $text .= "**Top Highlights:**\n";
        foreach (array_slice($dest['highlights'], 0, 4) as $h) {
            $text .= "☑ $h\n";
        }
        $text .= "\n[View full details](/destination/{$dest['id']}) • [Book this trip](/booking/{$dest['id']})";

        return [
            'intent' => 'destination_info',
            'text' => $text,
            'cards' => [$dest],
        ];
    }
}