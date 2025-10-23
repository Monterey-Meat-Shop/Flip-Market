<x-layouts.app>
<div class="max-w-4xl mx-auto p-4 sm:p-6 bg-gray-50 min-h-screen">
  <!-- Header -->
  <div class="mb-6">
    <a href="{{ route('my.orders') }}" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
      ← Back to Orders
    </a>
    <h2 class="text-2xl font-semibold text-gray-900">Return Request Details</h2>
  </div>

  <!-- Status Alert -->
  <div class="mb-6">
    @php
      $statusColors = [
        'pending' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
        'approved' => 'bg-blue-100 border-blue-400 text-blue-800',
        'rejected' => 'bg-red-100 border-red-400 text-red-800',
        'completed' => 'bg-green-100 border-green-400 text-green-800',
        'refunded' => 'bg-purple-100 border-purple-400 text-purple-800',
      ];
      $statusColor = $statusColors[$return->return_status] ?? 'bg-gray-100 border-gray-400 text-gray-800';
    @endphp
    
    <div class="border-l-4 p-4 {{ $statusColor }}">
      <div class="flex">
        <div class="flex-shrink-0">
          @if($return->return_status === 'pending')
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
          @elseif($return->return_status === 'approved')
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          @elseif($return->return_status === 'rejected')
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          @else
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          @endif
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium">
            Status: {{ ucfirst(str_replace('_', ' ', $return->return_status)) }}
          </h3>
          <div class="mt-2 text-sm">
            <p>{{ $return->return_status_label }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rest of your Blade content (Return Information, Returned Items, Timeline, etc.) -->
</div>
</x-layouts.app>
