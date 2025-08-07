@props(['id', 'name', 'description', 'price', 'image'])

<div
    class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 group cursor-pointer hover:shadow-lg transition-shadow">
    <div
        class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
        <img src="{{ $image }}" alt="{{ $name }}" class="w-16 h-16 object-contain" />
    </div>
    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ $name }}</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $description }}</p>
    <div class="flex items-center justify-between">
        <span class="font-bold text-lg text-zinc-900 dark:text-zinc-100">${{ $price }}</span>
        <a href="{{ url('/products/' . $id) }}">
            <flux:button size="sm" variant="filled">Add to Cart</flux:button>
        </a>

    </div>
</div>
