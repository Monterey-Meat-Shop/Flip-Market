@extends('layouts.app')

@section('content')
    <section class="w-full h-full px-10 py-16 flex justify-center">
        <div class="flex flex-row w-full max-w-7xl h-full">
            {{-- Left: Image Section --}}
            <div class="flex-1 flex h-full gap-5">
                {{-- Thumbnails --}}
                <div class="flex flex-col justify-between w-1/4 h-full gap-3">
                    @for ($i = 0; $i < 4; $i++)
                        <img class="aspect-square object-cover w-full rounded-lg border hover:scale-105 transition"
                            src="{{ $product->image_url[$i] ?? 'https://via.placeholder.com/300' }}"
                            alt="{{ $product->name }} thumbnail {{ $i + 1 }}" />
                    @endfor
                </div>

                {{-- Main Image --}}
                <div class="w-3/4 h-full">
                    <img class="h-full w-full object-cover rounded-lg border"
                        src="{{ $product->image_url[0] ?? 'https://via.placeholder.com/600x800' }}"
                        alt="{{ $product->name }}" />
                </div>
            </div>

            {{-- Right: Product Content --}}
            <div class="flex-1 px-8 h-full overflow-y-auto">
                <h1 class="text-3xl font-bold mb-8">{{ $product->name }}</h1>
                <p class="text-2xl font-semibold text-green-600 mb-12">₱ {{ $product->price ?? '0.00' }}</p>
                <p class="text-lg text-gray-700 mb-6">{{ $product->description ?? 'No description available.' }}</p>
            </div>
        </div>
    </section>
@endsection
