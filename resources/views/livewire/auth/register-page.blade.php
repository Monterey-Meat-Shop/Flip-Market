<main class="w-full max-w-5xl mx-auto relative z-10 py-4 px-4">

  {{-- ✅ Flash Message for Verification Notice --}}
  @if (session('verification_notice'))
    <div 
      x-data="{ show: true }" 
      x-init="setTimeout(() => show = false, 6000)" 
      x-show="show"
      x-transition
      class="mb-4 p-3 rounded-lg bg-green-100 border border-green-300 text-green-800 text-sm font-medium shadow"
    >
        {{ session('verification_notice') }}
    </div>
  @endif

  <form wire:submit.prevent="register">

    <!-- Account Information Card -->
    <div class="glass-effect rounded-2xl shadow-xl mb-4 overflow-hidden card-hover">
      <div class="bg-gray-900 p-2.5">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h2 class="text-lg font-bold text-white">Account Information</h2>
        </div>
      </div>

      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">

        <!-- First Name -->
        <div class="relative group">
          <label for="FirstName" class="block text-xs font-semibold mb-1 text-gray-700">First Name</label>
          <input type="text" id="FirstName" wire:model="firstname"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('firstname')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Last Name -->
        <div class="relative group">
          <label for="LastName" class="block text-xs font-semibold mb-1 text-gray-700">Last Name</label>
          <input type="text" id="LastName" wire:model="lastname"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('lastname')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Email -->
        <div class="relative group">
          <label for="email" class="block text-xs font-semibold mb-1 text-gray-700">Email</label>
          <input type="email" id="email" wire:model="email"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Phone -->
        <div class="relative group">
          <label for="phone" class="block text-xs font-semibold mb-1 text-gray-700">Phone</label>
          <input type="text" id="phone" wire:model="phone"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('phone')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Password -->
        <div class="relative group" x-data="{ showPassword: false }">
          <label for="password" class="block text-xs font-semibold mb-1 text-gray-700">Password</label>
          <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" id="password" wire:model="password"
              class="w-full py-2 px-3 pr-10 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
            <button type="button" @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600 focus:outline-none transition">
              <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                     4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 
                     0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 
                     0 114.243 4.243M3 3l18 18" />
              </svg>
            </button>
          </div>
          @error('password')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- Confirm Password -->
        <div class="relative group" x-data="{ showConfirmPassword: false }">
          <label for="password_confirmation" class="block text-xs font-semibold mb-1 text-gray-700">Confirm Password</label>
          <div class="relative">
            <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation"
              wire:model="password_confirmation"
              class="w-full py-2 px-3 pr-10 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600 focus:outline-none transition">
              <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 
                     0 8.268-2.943 9.542-7-1.274 
                     4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg x-show="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 
                     0-8.268-2.943-9.543-7a9.97 9.97 0 
                     011.563-3.029m5.858.908a3 3 0 114.243 4.243M3 3l18 18" />
              </svg>
            </button>
          </div>
          @error('password_confirmation')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </div>

    <!-- Billing & Shipping Information Card -->
    <div class="glass-effect rounded-2xl shadow-xl mb-4 overflow-hidden card-hover">
      <div class="bg-gray-900 p-2.5">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 
                   0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <h2 class="text-lg font-bold text-white">Billing & Shipping Information</h2>
        </div>
      </div>

      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="relative group">
          <label for="postal_code" class="block text-xs font-semibold mb-1 text-gray-700">Postal Code</label>
          <input type="text" id="postal_code" wire:model="postal_code"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('postal_code')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="relative group">
          <label for="address_line_1" class="block text-xs font-semibold mb-1 text-gray-700">Address Line 1</label>
          <input type="text" id="address_line_1" wire:model="address_line_1"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('address_line_1')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="relative group">
          <label for="city" class="block text-xs font-semibold mb-1 text-gray-700">City</label>
          <input type="text" id="city" wire:model="city"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('city')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="relative group">
          <label for="province" class="block text-xs font-semibold mb-1 text-gray-700">Province</label>
          <input type="text" id="province" wire:model="province"
            class="w-full py-2 px-3 rounded-lg border-2 border-gray-200 text-gray-800 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none input-focus" />
          @error('province')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </div>

   <!-- Action Buttons -->
<div class="flex flex-col sm:flex-row justify-between items-center gap-3">

  <!-- 🔙 Back to Login Button (Now same design as Create Account) -->
  <a href="/login"
     class="py-2.5 px-8 inline-flex justify-center items-center gap-x-2 
            text-sm font-bold rounded-full bg-blue-600 text-white 
            hover:bg-blue-700 shadow-lg hover:shadow-xl transition 
            btn-hover border-2 border-blue-200">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12H3m6 6l-6-6 6-6" />
    </svg>
    <span>Back to Login</span>
  </a>

  <!-- ✅ Create Account Button -->
  <button type="submit"
    class="py-2.5 px-8 inline-flex justify-center items-center gap-x-2 
           text-sm font-bold rounded-full bg-blue-600 text-white 
           hover:bg-blue-700 shadow-lg hover:shadow-xl transition 
           btn-hover border-2 border-blue-200">
    <span>Create Account</span>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 7l5 5m0 0l-5 5m5-5H6" />
    </svg>
  </button>

</div>

  </form>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); }
    .input-focus { transition: all 0.3s ease; }
    .input-focus:focus { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,0.15); }
    .btn-hover { transition: all 0.3s ease; }
    .btn-hover:hover { transform: translateY(-2px); }
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { box-shadow: 0 12px 28px rgba(0,0,0,0.08); }
  </style>
</main>
