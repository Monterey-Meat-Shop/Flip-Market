<main x-data="{ tab: 'profile' }" class="w-full max-w-6xl mx-auto p-6 min-h-screen">
  
  <!-- Success/Error Messages -->
  @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif

  @if (session()->has('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ session('error') }}
    </div>
  @endif

  <!-- Tabs -->
  <nav class="flex border-b border-gray-200 mb-6 space-x-6">
    <button 
      @click="tab = 'profile'"
      :class="tab === 'profile' ? 'text-blue-600 border-b-2 border-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600 font-medium'"
      class="pb-2"
    >
      Profile
    </button>
    <button 
      @click="tab = 'password'"
      :class="tab === 'password' ? 'text-blue-600 border-b-2 border-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600 font-medium'"
      class="pb-2"
    >
      Change Password
    </button>
  </nav>

  <!-- Profile Form -->
  <form x-show="tab === 'profile'" x-cloak wire:submit.prevent="saveProfile">
    <div class="flex items-center gap-3 mb-6">
      <h2 class="text-2xl font-bold text-gray-800">My Account</h2>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-gray-800 font-semibold">Account Information</h2>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- First Name -->
        <div>
          <label for="firstname" class="block text-sm mb-2 text-gray-700">First Name</label>
          <input id="firstname" type="text" wire:model="firstname"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('firstname') border-red-500 @enderror">
          @error('firstname') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- Last Name -->
        <div>
          <label for="lastname" class="block text-sm mb-2 text-gray-700">Last Name</label>
          <input id="lastname" type="text" wire:model="lastname"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lastname') border-red-500 @enderror">
          @error('lastname') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- Email (full width) -->
        <div class="col-span-2">
          <label for="email" class="block text-sm mb-2 text-gray-700">Email</label>
          <input id="email" type="email" wire:model="email"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
          @error('email') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- Phone -->
        <div class="col-span-2">
          <label for="phone" class="block text-sm mb-2 text-gray-700">Phone</label>
          <input type="text" id="phone" wire:model="phone"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
          @error('phone') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
      </div>   
    </div>

    <!-- Billing & Shipping -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
      <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-gray-800 font-semibold">Billing & Shipping Information</h2>
        <button type="button" wire:click="showNewAddressForm" 
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
          Add New Address
        </button>
      </div>
      
      <!-- Address Form (shown when adding/editing) -->
      @if($showAddressForm)
      <div class="p-6 border-b bg-gray-50">
        <h3 class="text-sm font-semibold mb-4">{{ $editingAddressId ? 'Edit Address' : 'New Address' }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Address Line 1 -->
          <div>
            <label for="address_line_1" class="block text-sm mb-2 text-gray-700">Address <span class="text-red-500">*</span></label>
            <input type="text" id="address_line_1" wire:model="address_line_1"
              class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('address_line_1') border-red-500 @enderror">
            @error('address_line_1') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
          </div>
          
          <!-- City -->
          <div>
            <label for="city" class="block text-sm mb-2 text-gray-700">City <span class="text-red-500">*</span></label>
            <input type="text" id="city" wire:model="city"
              class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('city') border-red-500 @enderror">
            @error('city') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
          </div>
          
          {{-- <!-- Address Line 2 -->
          <div>
            <label for="address_line_2" class="block text-sm mb-2 text-gray-700">Address Line 2</label>
            <input type="text" id="address_line_2" wire:model="address_line_2"
              class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('address_line_2') border-red-500 @enderror">
            @error('address_line_2') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
          </div> --}}
          
          <!-- Province -->
          <div>
            <label for="province" class="block text-sm mb-2 text-gray-700">Province <span class="text-red-500">*</span></label>
            <input type="text" id="province" wire:model="province"
              class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('province') border-red-500 @enderror">
            @error('province') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
          </div>

          <!-- Postal Code -->
          <div>
            <label for="postal_code" class="block text-sm mb-2 text-gray-700">Postal Code <span class="text-red-500">*</span></label>
            <input type="text" id="postal_code" wire:model="postal_code"
              class="w-full py-3 px-4 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-500 focus:ring-blue-500 @error('postal_code') border-red-500 @enderror">
            @error('postal_code') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <button type="button" wire:click="saveAddress" 
                  class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
            Save Address
          </button>
          <button type="button" wire:click="cancelAddressForm" 
                  class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400">
            Cancel
          </button>
        </div>
      </div>
      @endif

      <!-- Saved Addresses List -->
      <div class="p-6">
        @if($addresses && $addresses->count() > 0)
          <div class="space-y-4">
            @foreach($addresses as $address)
            <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-start">
              <div>
                <p class="font-medium text-gray-800">{{ $address->address_line_1 }}</p>
                @if($address->address_line_2)
                  <p class="text-sm text-gray-600">{{ $address->address_line_2 }}</p>
                @endif
                <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
              </div>
              <div class="flex gap-2">
                <button type="button" wire:click="editAddress({{ $address->addressID }})" 
                        class="text-blue-600 hover:text-blue-800 text-sm">
                  Edit
                </button>
                <button type="button" wire:click="deleteAddress({{ $address->addressID }})"
                        wire:confirm="Are you sure you want to delete this address?"
                        class="text-red-600 hover:text-red-800 text-sm">
                  Delete
                </button>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <p class="text-gray-500 text-sm">No addresses saved yet. Click "Add New Address" to add one.</p>
        @endif
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end mt-6">
      <button type="submit"
              wire:loading.attr="disabled"
              wire:loading.class="opacity-50 cursor-not-allowed"
              class="py-3 px-6 inline-flex justify-center items-center gap-x-2 
                     text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-800 
                     shadow-md hover:shadow-lg transition">
        <span wire:loading.remove>Save Changes</span>
        <span wire:loading>Saving...</span>
      </button>
    </div>
  </form>

  <!-- Change Password Form -->
  <form x-show="tab === 'password'" x-cloak wire:submit.prevent="changePassword">
    <div class="flex items-center gap-3 mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Change Password</h2>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-gray-800 font-semibold">Update Password</h2>
      </div>

      <div class="p-6 grid grid-cols-1 gap-4">
        <!-- Current Password -->
        <div>
          <label for="current_password" class="block text-sm mb-2 text-gray-700">Current Password</label>
          <input id="current_password" type="password" wire:model="current_password"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('current_password') border-red-500 @enderror">
          @error('current_password') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- New Password -->
        <div>
          <label for="new_password" class="block text-sm mb-2 text-gray-700">New Password</label>
          <input id="new_password" type="password" wire:model="new_password"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('new_password') border-red-500 @enderror">
          @error('new_password') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="confirm_password" class="block text-sm mb-2 text-gray-700">Confirm Password</label>
          <input id="confirm_password" type="password" wire:model="confirm_password"
            class="w-full py-3 px-4 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('confirm_password') border-red-500 @enderror">
          @error('confirm_password') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- Change Button -->
    <div class="flex justify-end mt-6">
      <button type="submit"
              wire:loading.attr="disabled"
              wire:loading.class="opacity-50 cursor-not-allowed"
              class="py-3 px-6 inline-flex justify-center items-center gap-x-2 
                     text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-800 
                     shadow-md hover:shadow-lg transition">
        <span wire:loading.remove>Change Password</span>
        <span wire:loading>Changing...</span>
      </button>
    </div>
  </form>
</main>