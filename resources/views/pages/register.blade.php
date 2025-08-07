<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign Up - FlipMarket</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="bg-zinc-100 dark:bg-zinc-900 min-h-screen flex items-center justify-center">

    <div
        class="w-full max-w-md bg-white dark:bg-zinc-800 p-8 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-md">
        <flux:heading size="xl" level="1" class="mb-6 text-center">Create your account</flux:heading>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <flux:input name="name" type="text" placeholder="Full Name" icon="user" required autofocus
                class="w-full" />

            <flux:input name="email" type="email" placeholder="Email" icon="envelope" required class="w-full" />

            <flux:input name="password" type="password" placeholder="Password" icon="lock-closed" required
                class="w-full" />

            <flux:input name="password_confirmation" type="password" placeholder="Confirm Password" icon="lock-closed"
                required class="w-full" />

            <flux:button type="submit" variant="primary" class="w-full">Sign Up</flux:button>
        </form>

        <div class="text-center text-sm text-gray-600 dark:text-gray-300 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
        </div>
    </div>

    @fluxScripts
</body>

</html>
