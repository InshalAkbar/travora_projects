<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\ExploreController;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Show booking form for a destination
     */
    public function create($id)
    {
        $destinations = ExploreController::getDestinations();
        $destination = collect($destinations)->firstWhere('id', (int)$id);

        if (!$destination) {
            abort(404, 'Destination not found');
        }

        return view('booking', compact('destination'));
    }

    /**
     * Store booking in database
     */
    public function store(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'destination_id' => 'required|integer',
            'full_name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|min:7|max:20',
            'travel_date' => 'required|date|after_or_equal:today',
            'return_date' => 'nullable|date|after:travel_date',
            'travelers' => 'required|integer|min:1|max:20',
            'travel_style' => 'nullable|string|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        // Get destination info
        $destinations = ExploreController::getDestinations();
        $destination = collect($destinations)->firstWhere('id', (int)$validated['destination_id']);

        if (!$destination) {
            return back()->withErrors(['destination_id' => 'Invalid destination'])->withInput();
        }

        // Calculate total price
        $basePrice = (float) str_replace(['$', ','], '', $destination['price']);
        $totalPrice = $basePrice * $validated['travelers'];

        // Generate reference
        $reference = Booking::generateReference();

        // Save booking
        $booking = Booking::create([
            'user_id' => auth()->id(), // null agar guest hai
            'reference' => $reference,
            'destination_id' => $destination['id'],
            'destination_name' => $destination['title'],
            'destination_country' => $destination['country'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'travel_date' => $validated['travel_date'],
            'return_date' => $validated['return_date'] ?? null,
            'travelers' => $validated['travelers'],
            'travel_style' => $validated['travel_style'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        return redirect()->route('booking.success', $booking->reference);
    }

    /**
     * Show success page
     */
    public function success($reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();

        return view('booking-success', compact('booking'));
    }

    /**
     * Calculate price via AJAX (for live preview)
     */
    public function calculatePrice(Request $request)
    {
        $id = (int) $request->input('destination_id');
        $travelers = (int) $request->input('travelers', 1);

        $destinations = ExploreController::getDestinations();
        $destination = collect($destinations)->firstWhere('id', $id);

        if (!$destination) {
            return response()->json(['error' => 'Invalid destination'], 400);
        }

        $basePrice = (float) str_replace(['$', ','], '', $destination['price']);
        $total = $basePrice * $travelers;

        return response()->json([
            'base_price' => $basePrice,
            'travelers' => $travelers,
            'total' => $total,
            'formatted_total' => '$' . number_format($total, 0),
        ]);
    }
}