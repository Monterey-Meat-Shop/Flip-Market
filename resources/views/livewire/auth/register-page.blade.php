<main class="w-full max-w-5xl mx-auto relative z-10 py-6 px-4">

  {{-- Floating Toast Notification --}}
@if ($successMessage)
  <div 
    x-data="{ show: true }" 
    x-init="
      // hide toast after 5s
      setTimeout(() => show = false, 5000);

      // redirect after 2s
      setTimeout(() => window.location.href = '/login', 2000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-lg p-4 flex items-start gap-3"
  >
    <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>

    <div class="flex-1 text-sm font-medium leading-relaxed">
      {{ $successMessage }}
    </div>

    <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 focus:outline-none">
      ✖
    </button>
  </div>
@endif

  <form wire:submit.prevent="register" class="space-y-6">

    <!-- Account Information Card -->
    <section class="rounded-2xl shadow-sm overflow-hidden border border-gray-200 bg-white">
      <div class="px-4 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-white bg-opacity-10 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div>
          <h2 class="text-lg font-semibold">Account Information</h2>
          <p class="text-xs text-slate-300">Your account credentials</p>
        </div>
      </div>

      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">

        <!-- First Name -->
        <div class="relative">
          <label for="FirstName" class="block text-xs font-semibold mb-1 text-slate-700">First Name</label>
          <input type="text" id="FirstName" wire:model="firstname"
            class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" />
          @error('firstname') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Last Name -->
        <div class="relative">
          <label for="LastName" class="block text-xs font-semibold mb-1 text-slate-700">Last Name</label>
          <input type="text" id="LastName" wire:model="lastname"
            class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" />
          @error('lastname') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div class="relative">
          <label for="email" class="block text-xs font-semibold mb-1 text-slate-700">Email</label>
          <input type="email" id="email" wire:model="email"
            class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" />
          @error('email') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div class="relative">
          <label for="phone" class="block text-xs font-semibold mb-1 text-slate-700">Phone</label>

          <input
              type="text"
              id="phone"
              wire:model="phone"
              maxlength="11"
              pattern="[0-9]{11}"
              inputmode="numeric"
              oninput="this.value=this.value.replace(/[^0-9]/g,'').substring(0,11);"
              class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"
          />

          @error('phone') 
              <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> 
          @enderror
        </div>

        <!-- Password -->
        <div class="relative" x-data="{ showPassword: false }">
          <label class="block text-xs font-semibold mb-1 text-slate-700">Password</label>

          <div class="relative">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              wire:model="password"
              class="w-full py-2 px-3 pr-10 rounded-lg border border-gray-200 text-slate-900 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" 
            />

            <button type="button" @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">

              <!-- Eye -->
              <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.478 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.064 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
              </svg>

              <!-- Eye Slash -->
              <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 
                         0112 19c-4.478 
                         0-8.268-2.943-9.543-7
                         a9.97 9.97 0 011.563-3.029
                         m5.858.908a3 3 0 114.243 4.243
                         M3 3l18 18" />
              </svg>

            </button>
          </div>

          @error('password') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="relative" x-data="{ showConfirm: false }">
          <label class="block text-xs font-semibold mb-1 text-slate-700">Confirm Password</label>

          <div class="relative">
            <input 
              :type="showConfirm ? 'text' : 'password'" 
              wire:model="password_confirmation"
              class="w-full py-2 px-3 pr-10 rounded-lg border border-gray-200 text-slate-900 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" 
            />

            <button type="button" @click="showConfirm = !showConfirm"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">

              <!-- Eye -->
              <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.478 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.064 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
              </svg>

              <!-- Eye Slash -->
              <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 
                         0112 19c-4.478 
                         0-8.268-2.943-9.543-7
                         a9.97 9.97 0 
                         011.563-3.029
                         m5.858.908a3 3 0 
                         114.243 4.243M3 3l18 18" />
              </svg>
            </button>
          </div>

          @error('password_confirmation') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </section>




    <!-- Billing & Shipping Information Card -->
    <section class="rounded-2xl shadow-sm overflow-hidden border border-gray-200 bg-white">
      <div class="px-4 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-white bg-opacity-10 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 
                   0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <h2 class="text-lg font-semibold">Billing & Shipping Information</h2>
          <p class="text-xs text-slate-300">Where we’ll ship your order</p>
        </div>
      </div>

      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">

        <div>
          <label for="postal_code" class="block text-xs font-semibold mb-1 text-slate-700">Postal Code</label>
          <input type="text" id="postal_code" wire:model="postal_code"
            class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" />
          @error('postal_code') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="address_line_1" class="block text-xs font-semibold mb-1 text-slate-700">Address Line 1</label>
          <input type="text" id="address_line_1" wire:model="address_line_1"
            class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" />
          @error('address_line_1') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3">
          <!-- Province -->
          <div>
            <label for="province" class="block text-xs font-semibold mb-1 text-slate-700">Province</label>
            <select id="province" wire:model="province"
              class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
              <option value="">-- Select province --</option>
            </select>
            @error('province') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <!-- City -->
          <div>
            <label for="city" class="block text-xs font-semibold mb-1 text-slate-700">City / Municipality</label>
            <select id="city" wire:model="city"
              class="w-full py-2 px-3 rounded-lg border border-gray-200 text-slate-900 text-sm
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
              <option value="">-- Select city / municipality --</option>
            </select>
            @error('city') <p class="text-xxs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

      </div>
    </section>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
      <a href="/login"
         class="py-2.5 px-6 inline-flex justify-center items-center gap-x-2 
                text-sm font-semibold rounded-full bg-transparent text-slate-700 
                hover:bg-slate-50 border border-slate-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12H3m6 6l-6-6 6-6" />
        </svg>
        Back to Login
      </a>

      <button type="submit" wire:loading.attr="disabled"
        class="py-2.5 px-6 inline-flex justify-center items-center gap-x-2 
               text-sm font-semibold rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white 
               hover:opacity-95 shadow-lg transition">
        <span wire:loading.remove>Create Account</span>
        <span wire:loading>Creating...</span>
      </button>
    </div>

  </form>


  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    :root { --card-radius: 12px; }
    body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
    .text-xxs { font-size: 11px; }
    section { border-radius: var(--card-radius); overflow: hidden; }
    input:focus, select:focus { box-shadow: 0 6px 18px rgba(59,130,246,0.08); }
  </style>



  {{-- Province-City JS --}}
  <script>
    (function () {
      const DATA_URL = '/data/philippines.json';

      const provinceEl = document.getElementById('province');
      const cityEl = document.getElementById('city');

      const initialProvince = @json($province ?? '');
      const initialCity = @json($city ?? '');

      function clear(el) {
        while (el.options.length > 1) el.remove(1);
      }

      function populateProvinces(data) {
        clear(provinceEl);
        Object.keys(data).sort().forEach(p => {
          const opt = new Option(p, p);
          if (p === initialProvince) opt.selected = true;
          provinceEl.add(opt);
        });
        provinceEl.dispatchEvent(new Event('change'));
      }

      function populateCities(data, province) {
        clear(cityEl);
        if (!province || !data[province]) return;
        data[province].forEach(c => {
          const opt = new Option(c, c);
          if (c === initialCity) opt.selected = true;
          cityEl.add(opt);
        });
      }

      fetch(DATA_URL)
        .then(r => r.json())
        .then(data => {
          populateProvinces(data);
          provinceEl.addEventListener('change', () => populateCities(data, provinceEl.value));
          if (initialProvince) populateCities(data, initialProvince);
        })
        .catch(() => console.warn("Location JSON missing"));
    })();
  </script>

</main>
