@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6 bg-gray-50 min-h-screen">
  <!-- Steps -->
  <div class="flex justify-center">
    <div class="flex justify-between items-center mb-4 max-w-lg w-full">
      <div class="flex justify-center items-center space-x-2">
        <div class="w-6 h-6 flex items-center justify-center bg-green-600 text-white rounded-full text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-400">Return reason</span>
      </div>
      <div class="h-px flex-1 bg-green-600 mx-2"></div>
      <div class="flex justify-center items-center space-x-2">
        <div class="w-6 h-6 flex items-center justify-center bg-green-600 text-white rounded-full text-sm">2</div>
        <span class="text-sm font-medium text-green-600">Confirmation</span>
      </div>
    </div>
  </div>

  <!-- Success Message -->
  <div class="bg-white rounded-lg shadow-sm p-8 text-center my-8">
    <div class="flex justify-center mb-4">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">Return Request Submitted!</h2>
    <p class="text-gray-600 mb-6">Your return request has been successfully submitted and is now under review.</p>

    @if(session('success'))
      <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
      </div>
    @endif

    <!-- What's Next Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-left">
      <h3 class="text-lg font-semibold text-gray-900 mb-3">What happens next?</h3>
      <ul class="space-y-3 text-gray-700">
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Our team will review your return request within 1-2 business days</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <span>You will receive an email notification about the status of your request</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
          </svg>
          <span>If approved, we'll provide instructions for returning the product</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Your refund will be processed once we receive the returned product</span>
        </li>
      </ul>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('my.orders') }}" class="px-6 py-3 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors font-medium">
        View My Orders
      </a>
      <a href="{{ route('landingpage') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
        Continue Shopping
      </a>
    </div>
  </div>

  <!-- Help Section -->
  <div class="bg-white rounded-lg shadow-sm p-6 text-center">
    <p class="text-gray-600 mb-2">Need help with your return?</p>
    <p class="text-sm text-gray-500">
      Contact our customer support at 
      <a href="mailto:support@example.com" class="text-blue-600 hover:underline">support@example.com</a>
      or call us at 
      <a href="tel:+1234567890" class="text-blue-600 hover:underline">+123-456-7890</a>
    </p>
  </div>
</div>
@endsection
  <!-- Steps -->
  <div class="flex justify-center">
    <div class="flex justify-between items-center mb-4 max-w-lg w-full">
      <div class="flex justify-center items-center space-x-2">
        <div class="w-6 h-6 flex items-center justify-center bg-green-600 text-white rounded-full text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-400">Return reason</span>
      </div>
      <div class="h-px flex-1 bg-green-600 mx-2"></div>
      <div class="flex justify-center items-center space-x-2">
        <div class="w-6 h-6 flex items-center justify-center bg-green-600 text-white rounded-full text-sm">2</div>
        <span class="text-sm font-medium text-green-600">Confirmation</span>
      </div>
    </div>
  </div>

  <!-- Success Message -->
  <div class="bg-white rounded-lg shadow-sm p-8 text-center my-8">
    <div class="flex justify-center mb-4">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">Return Request Submitted!</h2>
    <p class="text-gray-600 mb-6">Your return request has been successfully submitted and is now under review.</p>

    @if(session('success'))
      <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
      </div>
    @endif

    <!-- What's Next Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-left">
      <h3 class="text-lg font-semibold text-gray-900 mb-3">What happens next?</h3>
      <ul class="space-y-3 text-gray-700">
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Our team will review your return request within 1-2 business days</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <span>You will receive an email notification about the status of your request</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
          </svg>
          <span>If approved, we'll provide instructions for returning the product</span>
        </li>
        <li class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Your refund will be processed once we receive the returned product</span>
        </li>
      </ul>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('my.orders') }}" class="px-6 py-3 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors font-medium">
        View My Orders
      </a>
      <a href="{{ route('landingpage') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
        Continue Shopping
      </a>
    </div>
  </div>

  <!-- Help Section -->
  <div class="bg-white rounded-lg shadow-sm p-6 text-center">
    <p class="text-gray-600 mb-2">Need help with your return?</p>
    <p class="text-sm text-gray-500">
      Contact our customer support at 
      <a href="mailto:support@example.com" class="text-blue-600 hover:underline">support@example.com</a>
      or call us at 
      <a href="tel:+1234567890" class="text-blue-600 hover:underline">+123-456-7890</a>
    </p>
  </div>
</div>