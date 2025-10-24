<div class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-6 glass-effect relative overflow-hidden">

    {{-- ✅ Success Overlay Animation --}}
    @if (session('status'))
      <div 
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition
        class="absolute inset-0 flex flex-col items-center justify-center bg-green-50 bg-opacity-90 rounded-2xl z-20">
        <svg class="w-14 h-14 text-green-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-green-700 font-semibold mt-3 text-lg">Reset link sent successfully!</p>
        <p class="text-sm text-green-600 mt-1">Check your email inbox or spam folder.</p>
      </div>
    @endif

    {{-- ❌ Error Alert --}}
    @if (session('error'))
      <div 
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition
        class="absolute top-4 left-1/2 -translate-x-1/2 bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded-md text-sm font-medium shadow-md z-20">
        {{ session('error') }}
      </div>
    @endif

    <h2 class="text-xl font-bold text-center text-gray-900 mb-4">Forgot Password</h2>
    <p class="text-sm text-gray-600 text-center mb-6">
      Enter your registered email below, and we’ll send a password reset link.
    </p>

    <form wire:submit.prevent="sendResetLink" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
        <input type="email" id="email" wire:model="email"
          class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- 💫 Animated Submit Button --}}
      <button type="submit"
        class="w-full py-2.5 bg-blue-600 text-white font-semibold rounded-full
               hover:bg-blue-700 transition-all shadow-lg flex justify-center items-center gap-2 relative">

        {{-- Spinner (shows when sending) --}}
        <svg wire:loading wire:target="sendResetLink"
             class="animate-spin h-5 w-5 text-white absolute left-5"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>

        {{-- Text changes during load --}}
        <span wire:loading.remove wire:target="sendResetLink">Send Reset Link</span>
        <span wire:loading wire:target="sendResetLink">Sending...</span>
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
