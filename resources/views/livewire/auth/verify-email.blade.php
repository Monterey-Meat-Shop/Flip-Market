<main class="w-full max-w-lg mx-auto mt-10 relative z-10 py-6 px-6">
    <div class="glass-effect rounded-2xl shadow-xl p-6 text-center">

        <h1 class="text-2xl font-bold text-gray-900 mb-3">Verify Your Email</h1>

        @if (session('status') === 'verification-link-sent')
            <div 
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 6000)"
                x-show="show"
                x-transition
                class="mb-4 p-3 bg-green-100 text-green-800 border border-green-300 rounded-lg font-medium text-sm">
                ✅ A new verification link has been sent to your email address.
            </div>
        @endif

        <p class="text-gray-700 text-sm mb-6 leading-relaxed">
            Before continuing, please check your email for a verification link.<br>
            If you did not receive it, click the button below to request another.
        </p>

        <div class="flex flex-col gap-3">
            <button wire:click="resendVerification"
                class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full shadow">
                Resend Verification Email
            </button>

            <button wire:click="logout"
                class="py-2.5 px-5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full font-semibold">
                Log Out
            </button>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
    </style>
</main>
