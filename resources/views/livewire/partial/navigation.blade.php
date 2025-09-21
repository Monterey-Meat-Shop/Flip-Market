<div>
  <!-- Header -->
  <header class="flex z-40 sticky top-0 flex-wrap md:justify-start md:flex-nowrap w-full bg-white text-sm py-3 md:py-0 dark:bg-gray-800 shadow-md transition-all duration-300" id="pageContent">
    <nav class="max-w-[85rem] w-full mx-auto px-4 md:px-6 lg:px-8" aria-label="Global">
      <div class="relative md:flex md:items-center md:justify-between">
        <div class="flex items-center justify-between">
          <a class="flex-none text-xl font-semibold dark:text-white" href="/" aria-label="Brand">FLIPMARKET</a>
          <div class="md:hidden">
            <button type="button" class="hs-collapse-toggle flex justify-center items-center w-9 h-9 rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 dark:text-white dark:border-gray-700 dark:hover:bg-gray-700" data-hs-collapse="#navbar-collapse-with-animation" aria-controls="navbar-collapse-with-animation">
              <svg class="hs-collapse-open:hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <line x1="3" x2="21" y1="6" y2="6"/>
                <line x1="3" x2="21" y1="12" y2="12"/>
                <line x1="3" x2="21" y1="18" y2="18"/>
              </svg>
              <svg class="hs-collapse-open:block hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M18 6 6 18"/>
                <path d="m6 6 12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Navigation -->
        <div id="navbar-collapse-with-animation" class="hs-collapse hidden transition-all duration-300 basis-full grow md:block">
          <div class="flex flex-col mt-5 md:flex-row md:items-center md:justify-end md:gap-x-7 md:mt-0">
            <a class="font-medium text-blue-600 py-3 md:py-6" href="/">Home</a>
            <a class="font-medium text-gray-500 hover:text-gray-400 py-3 md:py-6" href="/brands">Brands</a>
            <a class="font-medium text-gray-500 hover:text-gray-400 py-3 md:py-6" href="/categories">Categories</a>
            <a class="font-medium text-gray-500 hover:text-gray-400 py-3 md:py-6" href="/products">Products</a>
            <a class="font-medium flex items-center text-gray-500 hover:text-gray-400 py-3 md:py-6" href="/cart">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
              </svg>
              <span class="mr-1">Cart</span>
              <span class="py-0.5 px-1.5 rounded-full text-xs font-medium bg-blue-50 border border-blue-200 text-blue-600">4</span>
            </a>
            <div class="pt-3 md:pt-0">
              <button id="openLogin" type="button" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                Log in
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Login Modal -->
  <div id="loginModal" class="fixed inset-0 hidden items-center justify-center z-[1000] bg-black/40 backdrop-blur-sm">
    <div class="relative w-full max-w-md bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
        <h2 class="text-2xl font-bold text-white">Welcome Back 👋</h2>
        <p class="text-sm text-blue-100 mt-1">Log in to continue to FlipMarket</p>
      </div>

      <!-- Close Button -->
      <button id="closeLogin" class="absolute top-4 right-4 text-white hover:text-gray-200 text-lg">✕</button>

      <!-- Body -->
      <div class="p-6">
        <form class="space-y-5">
          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" placeholder="you@example.com" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input type="password" placeholder="••••••••" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
              <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2">Remember me</span>
            </label>
            <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
          </div>

          <!-- Submit -->
          <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-md transition">
            Log in
          </button>
        </form>

        <!-- Footer -->
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-6 text-center">
          Don’t have an account?
          <button id="switchToSignup" class="text-blue-600 hover:underline">Sign up</button>
        </p>
      </div>
    </div>
  </div>

  <!-- Signup Modal -->
  <div id="signupModal" class="fixed inset-0 hidden items-center justify-center z-[1000] bg-black/40 backdrop-blur-sm">
    <div class="relative w-full max-w-md bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 text-center">
        <h2 class="text-2xl font-bold text-white">Create Account ✨</h2>
        <p class="text-sm text-green-100 mt-1">Join FlipMarket today!</p>
      </div>

      <!-- Close Button -->
      <button id="closeSignup" class="absolute top-4 right-4 text-white hover:text-gray-200 text-lg">✕</button>

      <!-- Body -->
      <div class="p-6">
        <form class="space-y-5">
          <!-- Last Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
            <input type="text" placeholder="Doe" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
          </div>

          <!-- First Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
            <input type="text" placeholder="John" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" placeholder="you@example.com" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input type="password" placeholder="••••••••" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <input type="password" placeholder="••••••••" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
            <textarea placeholder="123 Main Street, City, Country" required
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
          </div>

          <!-- Submit -->
          <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold shadow-md transition">
            Sign Up
          </button>
        </form>

        <!-- Footer -->
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-6 text-center">
          Already have an account?
          <button id="switchToLogin" class="text-green-600 hover:underline">Log in</button>
        </p>
      </div>
    </div>
  </div>
</div>

<script>
  const loginModal = document.getElementById('loginModal');
  const signupModal = document.getElementById('signupModal');
  const openLogin = document.getElementById('openLogin');
  const closeLogin = document.getElementById('closeLogin');
  const closeSignup = document.getElementById('closeSignup');
  const switchToSignup = document.getElementById('switchToSignup');
  const switchToLogin = document.getElementById('switchToLogin');

  // Open Login
  openLogin.addEventListener('click', () => {
    loginModal.classList.remove('hidden');
    loginModal.classList.add('flex');
  });

  // Close Login
  closeLogin.addEventListener('click', () => {
    loginModal.classList.add('hidden');
    loginModal.classList.remove('flex');
  });

  // Close Signup
  closeSignup.addEventListener('click', () => {
    signupModal.classList.add('hidden');
    signupModal.classList.remove('flex');
  });

  // Switch to Signup
  switchToSignup.addEventListener('click', () => {
    loginModal.classList.add('hidden');
    signupModal.classList.remove('hidden');
    signupModal.classList.add('flex');
  });

  // Switch to Login
  switchToLogin.addEventListener('click', () => {
    signupModal.classList.add('hidden');
    loginModal.classList.remove('hidden');
    loginModal.classList.add('flex');
  });

  // Close modals when clicking outside
  window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
      loginModal.classList.add('hidden');
      loginModal.classList.remove('flex');
    }
    if (e.target === signupModal) {
      signupModal.classList.add('hidden');
      signupModal.classList.remove('flex');
    }
  });
</script>
