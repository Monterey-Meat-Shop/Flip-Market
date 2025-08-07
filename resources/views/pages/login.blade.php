<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - FlipMarket</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="bg-zinc-100 dark:bg-zinc-900 min-h-screen flex items-center justify-center">

    <div
        class="w-full max-w-md bg-white dark:bg-zinc-800 p-8 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-md">
        <flux:heading size="xl" level="1" class="mb-6 text-center">Login to FlipMarket</flux:heading>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <flux:input name="email" type="email" placeholder="Email" icon="envelope" required autofocus
                class="w-full" />

            <flux:input name="password" type="password" placeholder="Password" icon="lock-closed" required
                class="w-full" />

            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" class="form-checkbox text-blue-600" />
                    Remember me
                </label>
                {{-- <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Forgot password?</a> --}}
            </div>

            <flux:button type="submit" variant="primary" class="w-full">Login</flux:button>
        </form>

        <div class="text-center text-sm text-gray-600 dark:text-gray-300 mt-6">
            Don’t have an account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Sign up</a>
        </div>
    </div>

    @fluxScripts
</body>

</html>
