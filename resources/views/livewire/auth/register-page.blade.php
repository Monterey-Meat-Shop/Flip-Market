


<main class="w-full max-w-5xl mx-auto p-6 h-screen">
  <form wire:submit.prevent="register">
  <!-- User Information -->
    <div class="flex items-center gap-3 mb-6">
    <!-- Icon -->
    <svg class="w-8 h-8 " xmlns="http://www.w3.org/2000/svg" fill="none" 
         viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    <!-- Title -->
    <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200">
      <h2 class="text-gray-800 font-semibold">Account Information</h2>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

      <!-- First Name -->
      <div class="relative">
        <label for="FirstName" class="block text-sm mb-2 text-gray-700">First Name</label>
        <input type="text" id="FirstName" wire:model="firstname"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">


              @error('firstname')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

              @error('firstname')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror

      </div>

      <!-- Last Name -->
      <div class="relative">
        <label for="LastName" class="block text-sm mb-2 text-gray-700">Last Name</label>
        <input type="text" id="LastName" wire:model="lastname"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">


              @error('lastname')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

 
              @error('lastname')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      <!-- Email -->
      <div class="relative">
        <label for="email" class="block text-sm mb-2 text-gray-700">Email</label>
        <input type="email" id="email" wire:model="email" 
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

           
              @error('email')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('email')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      <!-- Password -->
      <div class="relative">
        <label for="password" class="block text-sm mb-2 text-gray-700">Password</label>
        <input type="password" id="password" wire:model="password"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                            
              @error('password')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('password')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>
    </div>
  </div>

  <!-- Customer Information -->
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200">
      <h2 class="text-gray-800 font-semibold">Billing & Shipping Information</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Phone -->
      <div class="relative">
        <label for="phone" class="block text-sm mb-2 text-gray-700">Phone</label>
        <input type="text" id="phone" wire:model="phone"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                            
              @error('phone')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('phone')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      <!-- Postal Code -->
      <div class="relative">
        <label for="postal_code" class="block text-sm mb-2 text-gray-700">Postal Code</label>
        <input type="text" id="postal_code" wire:model="postal_code"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                                 
              @error('postal_code')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('postal_code')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      <!-- Address Line 1 -->
      <div class="relative">
        <label for="address_line_1" class="block text-sm mb-2 text-gray-700">Address Line 1</label>
        <input type="text" id="address_line_1" wire:model="address_line_1"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                  @error('address_line_1')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('address_line_1')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      <!-- City -->
      <div class="relative">
        <label for="city" class="block text-sm mb-2 text-gray-700">City</label>
        <input type="text" id="city" wire:model="city"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                             @error('city')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('city')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>

      {{-- <!-- Address Line 2 -->
      <div class="relative">
        <label for="address_line_2" class="block text-sm mb-2 text-gray-700">Address Line 2</label>
        <input type="text" id="address_line_2" wire:model="address_line_2"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

                             @error('address_line_2')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror

      
              @error('address_line_2')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div> --}}

      <!-- Province -->
      <div class="relative">
        <label for="province" class="block text-sm mb-2 text-gray-700">Province</label>
        <input type="text" id="province" wire:model="province"
          class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 
                 focus:border-blue-500 focus:ring-blue-500">

              @error('province')
                  <div class="absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor"
                          viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                      </svg>
                  </div>
              @enderror
              @error('province')
                  <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
              @enderror
      </div>


    </div>
  </div>

<div class="flex justify-between items-center mt-6">
  <!-- go back -->
 <a href="/login" class="flex items-center text-sm font-medium text-blue-600 hover:underline">
    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" 
         viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12H3m6 6l-6-6 6-6"/>
    </svg>
    Back to Login
  </a>

  <!-- Create btn -->
  <button type="submit"
    class="py-3 px-6 inline-flex justify-center items-center gap-x-2 
           text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-800 shadow-md hover:shadow-lg transition hover:cursor-pointer">

    Create Account
  </button>

</div>



</form>

</main>
