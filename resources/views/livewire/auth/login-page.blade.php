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
            <div>
              <div class="flex justify-between items-center">
                <label for="password" class="block text-sm mb-2">Password</label>
                <a wire:navigate class="text-sm text-blue-600 decoration-2 hover:underline font-medium" href="/forgot-password">
                  Forgot password?
                </a>
              </div>
              <div class="relative">
                <input type="password" id="password" wire:model="password"
                  class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm 
                         focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
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
