<?php

namespace App\Livewire\Partial;

use App\Models\Notification_Customer;
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
            $this->notifications = Notification_Customer::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            // Count unread notifications
            $this->unreadCount = Notification_Customer::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
        }
    }

            public function markAllAsReadAndRedirect($url)
        {
            $userId = Auth::id();

            // Mark all unread as read
            Notification_Customer::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Refresh list and unread count
            $this->notifications = Notification_Customer::where('user_id', $userId)
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
