<div class="flex items-center">
    <span class="mr-1">Cart</span>
    @if($cartCount > 0)
        <span class="py-0.5 px-1.5 rounded-full text-xs font-medium bg-blue-50 border border-blue-200 text-blue-600">
            {{ $cartCount }}
        </span>
    @else
        <span class="py-0.5 px-1.5 rounded-full text-xs font-medium bg-gray-100 border border-gray-200 text-gray-600">
            0
        </span>
    @endif
</div>