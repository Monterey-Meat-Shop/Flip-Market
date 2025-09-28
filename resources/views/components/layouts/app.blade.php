<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'FLIPMARKET' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js']) 
        @livewireStyles
    </head>
    
    <body class=" dark:bg-slate-600">
        @livewire('partial.navigation')
        <main >
            
        {{ $slot }}
    </main>
        @livewire('partial.footer')
        @livewireScripts
        <script src="https://unpkg.com/preline/dist/preline.js"></script>
        
    </body> 
</html>


