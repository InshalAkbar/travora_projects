<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\UserActivity;
use App\Models\AiConversation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::sum('total_price'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'today_users' => User::whereDate('created_at', today())->count(),
            'active_today' => UserActivity::whereDate('created_at', today())
                                ->distinct('user_id')
                                ->count('user_id'),
            'total_ai_chats' => AiConversation::count(),
        ];

        $recentUsers = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBookings = Booking::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivities = UserActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentAiChats = AiConversation::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart data — bookings last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('M d'),
                'count' => Booking::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentBookings',
            'recentActivities',
            'recentAiChats',
            'chartData'
        ));
    }

    public function users()
    {
        $users = User::where('role', 'user')
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function bookings()
    {
        $bookings = Booking::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bookings', compact('bookings'));
    }

    public function aiChats()
    {
        $conversations = AiConversation::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.ai-chats', compact('conversations'));
    }

    /**
     * Update booking status (NEW)
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status;
        $newStatus = $request->input('status');

        $booking->update(['status' => $newStatus]);

        // Log activity
        UserActivity::create([
            'user_id' => auth()->id(),
            'action' => 'booking_status_update',
            'details' => "Booking {$booking->reference} status changed from {$oldStatus} to {$newStatus}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Booking status updated to " . ucfirst($newStatus),
                'status' => $newStatus,
                'reference' => $booking->reference,
            ]);
        }

        return back()->with('success', "Booking {$booking->reference} status updated to " . ucfirst($newStatus));
    }
}