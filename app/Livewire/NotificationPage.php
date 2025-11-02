<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\Notifications;
use Illuminate\Support\Facades\Auth;

class NotificationPage extends Component
{
    use WithPagination; 

    public $perPage = 10; 
    protected $paginationTheme = 'tailwind'; 

    public function getNotificationsProperty()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return Notifications::whereNull('id');
        }

        return Notifications::where('user_id', $customer->user_id)
            ->orderByDesc('created_at');
    }

    public function markAsRead($id)
    {
        $notification = Notifications::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
        }

        $this->resetPage();
    }

    public function markAllAsRead()
    {
        Notifications::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.notification-page', [
            'notifications' => $this->notifications->paginate($this->perPage)
        ]);
    }
}
