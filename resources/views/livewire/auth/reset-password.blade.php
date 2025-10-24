<div class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="max-w-md mx-auto mt-20 p-6 glass-effect rounded-2xl shadow-lg relative overflow-hidden">

    {{-- ✅ Success Animation --}}
    @if (session('success'))
      <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition
        class="absolute inset-0 flex flex-col items-center justify-center bg-green-50 bg-opacity-90 rounded-2xl z-20">
        <div class="flex flex-col items-center gap-3">
          <svg class="w-14 h-14 text-green-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <p class="text-green-700 font-semibold text-lg">{{ session('success') }}</p>
        </div>
      </div>
    @endif

    <h2 class="text-xl font-bold text-center text-gray-900 mb-4">Reset Your Password</h2>
    <p class="text-sm text-gray-600 text-center mb-6">
      Enter your new password below to regain access to your account.
    </p>

    @if (session('error'))
      <div class="bg-red-100 text-red-800 text-sm p-2.5 rounded mb-3 text-center">
        {{ session('error') }}
      </div>
    @endif

    <form wire:submit.prevent="resetPassword" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
        <input type="email" id="email" wire:model="email"
          class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="password" class="block text-sm font-semibold text-gray-700">New Password</label>
        <input type="password" id="password" wire:model="password"
          class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password</label>
        <input type="password" id="password_confirmation" wire:model="password_confirmation"
          class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
        @error('password_confirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <button type="submit"
        class="w-full py-2.5 bg-blue-600 text-white font-semibold rounded-full
               hover:bg-blue-700 transition-all shadow-lg">
        Reset Password
      </button>

      <div class="text-center mt-4">
        <a href="/login" class="text-blue-600 hover:underline text-sm font-semibold">
          ← Back to Login
        </a>
      </div>
    </form>
  </div>

  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    [x-cloak] { display: none !important; }
  </style>
</div>
