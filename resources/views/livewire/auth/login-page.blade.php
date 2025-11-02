<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto h-screen">
  <div class="flex h-full items-center">
    <main class="w-full max-w-lg mx-auto p-6">
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-4 sm:p-7">
          <div class="text-center">
            <h1 class="block text-2xl font-bold text-gray-800">Sign in</h1>
            <p class="mt-2 text-sm text-gray-600">
              Don't have an account yet?
              <a wire:navigate class="text-blue-600 decoration-2 hover:underline font-medium" href="/register">
                Sign up here
              </a>
            </p>
          </div>

          <hr class="my-5 border-slate-300">

          <!-- Form -->
          <form wire:submit.prevent="login" class="grid gap-y-4">
            <!-- Email -->
            <div>
              <div class="flex justify-between items-center">
                <label for="email" class="block text-sm mb-2">Email address</label>
              </div>
              <div class="relative">
                <input type="email" id="email" wire:model="email"
                  class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm 
                         focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
              </div>
              @error('email')
                <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
            </div>
            <!-- End Email -->

            <!-- Password -->
            <div x-data="{ showPassword: false }">
              <div class="flex justify-between items-center">
                <label for="password" class="block text-sm mb-2">Password</label>
                <a wire:navigate class="text-sm text-blue-600 decoration-2 hover:underline font-medium" href="/forgot-password">
                  Forgot password?
                </a>
              </div>
              <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" wire:model="password"
                  class="py-3 px-4 pr-12 block w-full border border-gray-200 rounded-lg text-sm 
                         focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                <button type="button" @click="showPassword = !showPassword" 
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                  <!-- Eye Icon (Show) -->
                  <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  <!-- Eye Slash Icon (Hide) -->
                  <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                  </svg>
                </button>
              </div>
              @error('password')
                <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
            </div>
            <!-- End Password -->

            <!-- Remember Me -->
            <div class="flex items-center">
              <input type="checkbox" id="remember" wire:model="remember" class="mr-2">
              <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>

            <!-- Submit -->
            <button type="submit"
              class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 
                     text-sm font-semibold rounded-lg border border-transparent 
                     bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
              Sign in
            </button>
          </form>
          <!-- End Form -->
        </div>
      </div>
    </main>
  </div>
</div>