<section class="bg-gray-50 min-h-screen py-8"> 
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Notifications</h2>
            <button wire:click="markAllAsRead"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                Mark all as read
            </button>
        </div>

        <div class="space-y-4">
            @forelse ($notifications as $note)
                <div class="p-5 border border-gray-300 shadow-md rounded-lg
                    {{ $note->is_read ? 'bg-white' : 'bg-blue-50' }}
                    hover:bg-gray-100 transition flex justify-between items-start">
                    
                    <div>
                        <h4 class="text-base font-semibold text-gray-800">
                            {{ $note->title }}
                        </h4>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $note->message }}
                        </p>

                        <span class="text-xs text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($note->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if (!$note->is_read)
                            <button wire:click="markAsRead({{ $note->id }})"
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow text-sm">
                                Mark as Read
                            </button>
                        @endif

                        <a href="{{ route('my.orders') }}"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-20">
                    No notifications found.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</section>
