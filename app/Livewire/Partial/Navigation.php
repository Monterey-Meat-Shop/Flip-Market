<?php

namespace App\Livewire\Partial;

use App\Models\Notifications;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Navigation extends Component
{   
    public $isAuthenticated = false;
 public $notifications = [];
    public $unreadCount = 0;

    public function mount()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            // Fetch latest 5 notifications
            $this->notifications = Notifications::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            // Count unread notifications
            $this->unreadCount = Notifications::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
        }
    }

            public function markAllAsReadAndRedirect($url)
        {
            $userId = Auth::id();

            // Mark all unread as read
            Notifications::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Refresh list and unread count
            $this->notifications = Notifications::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            $this->unreadCount = 0;

            // Redirect to page
            return redirect($url);
        }


    public function render()
    {
        
        return view('livewire.partial.navigation');
    }

}
