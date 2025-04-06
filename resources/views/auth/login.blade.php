<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Pemesanan Ayam - Kenangan Senja</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-red-900 to-white flex items-center justify-center">
  <div class="flex max-w-4xl w-full rounded-lg shadow-lg overflow-hidden">
    <!-- Left Side: Login Form -->
    <div class="bg-red-600 text-white p-8 flex-1">
      <h1 class="text-2xl font-bold mb-4 text-center">Pemesanan Ayam</h1>
      <hr class="border-white mb-4">
      <p class="mb-6 text-center">Selamat Datang di UD. Ayam Potong Rizky</p>

      @if (session('error'))
        <div class="bg-red-800 text-white p-3 rounded-lg mb-4">
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="flex flex-col">
        @csrf

        <!-- Email -->
        <label for="email" class="mb-2">Email</label>
        <input 
          id="email" 
          type="email" 
          name="email" 
          placeholder="email" 
          required 
          class="w-full px-4 py-2 mb-4 bg-red-500 text-white placeholder-white rounded-lg focus:outline-none focus:bg-red-700"
        />

        <!-- Password -->
        <label for="password" class="mb-2">Password</label>
        <input 
          id="password" 
          type="password" 
          name="password" 
          placeholder="password" 
          required 
          class="w-full px-4 py-2 mb-6 bg-red-500 text-white placeholder-white rounded-lg focus:outline-none focus:bg-red-700"
        />

        <!-- Login Button -->
        <button 
          type="submit" 
          class="w-full bg-red-800 text-white py-2 rounded-lg hover:bg-red-900 transition duration-300"
        >
          Login
        </button>
      </form>
    </div>

    <!-- Right Side: Image -->
    <div class="bg-red-100 flex-1 flex items-center justify-center p-8">
        <img src="{{ asset('assets/images/LOGO AYAMKU.png') }}" alt="Ayam Potong Rizky" style="width: 80%; border-radius: 10px;">
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');

      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          if (input.value !== '') {
            input.classList.add('border-red-900');
          } else {
            input.classList.remove('border-red-900');
          }
        });

        input.addEventListener('focus', function() {
          input.classList.add('border-red-900');
        });
      });
    });
  </script>
</body>
</html>