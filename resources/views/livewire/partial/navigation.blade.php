<div>
  <!-- Header -->
  <header class="flex z-40 sticky top-0 flex-wrap md:justify-start md:flex-nowrap w-full bg-white text-sm py-3 md:py-0 shadow-md transition-all duration-300" id="pageContent">
    <nav class="max-w-[85rem] w-full mx-auto px-4 md:px-6 lg:px-8" aria-label="Global">
      <div class="relative md:flex md:items-center md:justify-between">
        <div class="flex items-center justify-between">
          <a class="flex-none text-xl font-semibold text-blue-700" href="/" aria-label="Brand">FLIPMARKET</a>
          <div class="md:hidden">
            <button type="button" class="hs-collapse-toggle flex justify-center items-center w-9 h-9 rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100" data-hs-collapse="#navbar-collapse-with-animation" aria-controls="navbar-collapse-with-animation">
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
            <a class="font-medium text-blue-700 py-3 md:py-6" href="/">Home</a>
            <a class="font-medium text-gray-600 hover:text-gray-500 py-3 md:py-6" href="/brands">Brands</a>
            <a class="font-medium text-gray-600 hover:text-gray-500 py-3 md:py-6" href="/categories">Categories</a>
            <a class="font-medium text-gray-600 hover:text-gray-500 py-3 md:py-6" href="/products">Products</a>
            <a class="font-medium flex items-center text-gray-600 hover:text-gray-500 py-3 md:py-6" href="/cart">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
              </svg>
              <span class="mr-1">Cart</span>
              <span class="py-0.5 px-1.5 rounded-full text-xs font-medium bg-blue-50 border border-blue-200 text-blue-700">4</span>
            </a>
            <div class="pt-3 md:pt-0">
              <button id="openLogin" type="button" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-700 text-white hover:bg-blue-600">
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
    <div class="relative w-full max-w-md p-2">
      <!-- Card -->
      <div role="dialog" aria-modal="true" aria-labelledby="login-title" class="rounded-2xl overflow-hidden shadow-2xl bg-white ring-1 ring-black/5 animate-[fadeIn_200ms_ease-out]">
        <!-- Accent strip -->
        <div class="h-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-600"></div>

        <div class="px-6 pt-6 pb-2">
          <h2 id="login-title" class="text-xl font-semibold text-gray-900">Welcome back</h2>
          <p class="mt-1 text-sm text-gray-500">Sign in to continue</p>
        </div>

        <!-- Body -->
        <div class="px-6 pb-6">
          <form class="space-y-4" method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="login_email">Email</label>
              <input id="login_email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username"
                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
              @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="loginPassword">Password</label>
              <div class="relative">
                <input id="loginPassword" name="password" type="password" placeholder="••••••••" required autocomplete="current-password"
                  class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                <button type="button" onclick="togglePassword('loginPassword')" class="absolute inset-y-0 right-2.5 flex items-center text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 text-sm text-gray-600">
                <input name="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                Remember me
              </label>
              <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:text-blue-600">Forgot password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
              Sign in
            </button>
          </form>

          <!-- Divider -->
          <div class="flex items-center my-5">
            <span class="flex-1 h-px bg-gray-200"></span>
            <span class="px-3 text-xs text-gray-400">OR</span>
            <span class="flex-1 h-px bg-gray-200"></span>
          </div>

          <!-- Social (Google only) -->
          <div>
            <a href="{{ url('/auth/google/redirect') }}" class="w-full rounded-lg py-2.5 flex items-center justify-center gap-2 border border-gray-300 bg-white text-gray-800 hover:bg-gray-50">
              <img alt="Google" class="w-5 h-5" src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/google.svg">
              <span class="text-sm font-medium">Continue with Google</span>
            </a>
          </div>

          <!-- Footer -->
          <p class="text-sm text-center mt-6 text-gray-600">
            New to FlipMarket?
            <button id="switchToSignup" class="font-medium text-blue-700 hover:text-blue-600">Create an account</button>
          </p>
        </div>
      </div>

      <!-- Close -->
      <button id="closeLogin" class="absolute -top-3 -right-3 bg-white border rounded-full w-9 h-9 shadow flex items-center justify-center text-gray-700 hover:bg-gray-50" aria-label="Close">✕</button>
    </div>
  </div>

  <!-- Signup Modal -->
  <div id="signupModal" class="fixed inset-0 hidden items-center justify-center z-[1000] bg-black/40 backdrop-blur-sm">
    <div class="relative w-full max-w-md p-2">
      <div role="dialog" aria-modal="true" aria-labelledby="signup-title" class="rounded-2xl overflow-hidden shadow-2xl bg-white ring-1 ring-black/5 animate-[fadeIn_200ms_ease-out]">
        <!-- Accent strip -->
        <div class="h-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-600"></div>

        <div class="px-6 pt-6 pb-2">
          <h2 id="signup-title" class="text-xl font-semibold text-gray-900">Create your account</h2>
          <p class="mt-1 text-sm text-gray-500">It only takes a minute</p>
        </div>

        <div class="px-6 pb-6">
          <form class="space-y-4" method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="first_name">First Name</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="Juan" required
                  class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                @error('first_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="last_name">Last Name</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Dela Cruz" required
                  class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                @error('last_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email"
                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
              @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Passwords -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password</label>
                <div class="relative">
                  <input id="password" name="password" type="password" placeholder="Min. 8 characters" required autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                  <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-2.5 flex items-center text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="password_confirmation">Confirm Password</label>
                <div class="relative">
                  <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter password" required autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                  <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-2.5 flex items-center text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Terms -->
            <label class="flex items-start gap-2 text-sm text-gray-600">
              <input type="checkbox" name="terms" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-600" {{ old('terms') ? 'checked' : '' }} required>
              <span>I agree to the <a href="#" class="text-blue-700 hover:text-blue-600 font-medium">Terms</a> and <a href="#" class="text-blue-700 hover:text-blue-600 font-medium">Privacy Policy</a>.</span>
            </label>
            @error('terms') <p class="text-xs text-red-600 -mt-2">{{ $message }}</p> @enderror

            <!-- Submit -->
            <button type="submit" class="w-full py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
              Create account
            </button>
          </form>

          <!-- Divider -->
          <div class="flex items-center my-5">
            <span class="flex-1 h-px bg-gray-200"></span>
            <span class="px-3 text-xs text-gray-400">OR</span>
            <span class="flex-1 h-px bg-gray-200"></span>
          </div>

          <!-- Social (Google only) -->
          <div>
            <a href="{{ url('/auth/google/redirect') }}" class="w-full rounded-lg py-2.5 flex items-center justify-center gap-2 border border-gray-300 bg-white text-gray-800 hover:bg-gray-50">
              <img alt="Google" class="w-5 h-5" src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/google.svg">
              <span class="text-sm font-medium">Sign up with Google</span>
            </a>
          </div>

          <!-- Footer -->
          <p class="text-sm text-center mt-6 text-gray-600">
            Already have an account?
            <button id="switchToLogin" class="font-medium text-blue-700 hover:text-blue-600">Sign in</button>
          </p>
        </div>
      </div>

      <!-- Close -->
      <button id="closeSignup" class="absolute -top-3 -right-3 bg-white border rounded-full w-9 h-9 shadow flex items-center justify-center text-gray-700 hover:bg-gray-50" aria-label="Close">✕</button>
    </div>
  </div>
</div>

<!-- Auto-open signup modal if there are validation errors from /register -->
@if ($errors->any())
<script>
  window.addEventListener('load', () => {
    const modal = document.getElementById('signupModal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  });
</script>
@endif

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
    trapFocus(loginModal);
  });

  // Close Login
  closeLogin.addEventListener('click', () => {
    loginModal.classList.add('hidden');
    loginModal.classList.remove('flex');
    releaseFocus();
  });

  // Close Signup
  closeSignup.addEventListener('click', () => {
    signupModal.classList.add('hidden');
    signupModal.classList.remove('flex');
    releaseFocus();
  });

  // Switch to Signup
  switchToSignup?.addEventListener('click', () => {
    loginModal.classList.add('hidden');
    signupModal.classList.remove('hidden');
    signupModal.classList.add('flex');
    trapFocus(signupModal);
  });

  // Switch to Login
  switchToLogin?.addEventListener('click', () => {
    signupModal.classList.add('hidden');
    loginModal.classList.remove('hidden');
    loginModal.classList.add('flex');
    trapFocus(loginModal);
  });

  // Click outside to close
  window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
      loginModal.classList.add('hidden');
      loginModal.classList.remove('flex');
      releaseFocus();
    }
    if (e.target === signupModal) {
      signupModal.classList.add('hidden');
      signupModal.classList.remove('flex');
      releaseFocus();
    }
  });

  // Close on Escape
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (!loginModal.classList.contains('hidden')) {
        loginModal.classList.add('hidden');
        loginModal.classList.remove('flex');
      }
      if (!signupModal.classList.contains('hidden')) {
        signupModal.classList.add('hidden');
        signupModal.classList.remove('flex');
      }
      releaseFocus();
    }
  });

  // Toggle show/hide password
  function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
  }

  // Simple focus trap for accessibility
  let lastFocused = null;
  function trapFocus(modal) {
    lastFocused = document.activeElement;
    const focusable = modal.querySelectorAll('button, [href], input, textarea, select, [tabindex]:not([tabindex="-1"])');
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    first?.focus();

    function handleTab(e) {
      if (e.key !== 'Tab') return;
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    }
    modal.addEventListener('keydown', handleTab);
    modal._untrap = () => modal.removeEventListener('keydown', handleTab);
  }

  function releaseFocus() {
    const modals = [loginModal, signupModal];
    modals.forEach(m => m._untrap && m._untrap());
    lastFocused?.focus?.();
  }
</script>
