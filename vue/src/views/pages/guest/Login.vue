<template>
  <div
    class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat px-4"
    style="background-image: url('https://unwiku.ac.id/wp-content/uploads/2025/02/Drone-luas-minimize.jpg')"
  >
    <!-- Overlay --->
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative w-full max-w-md bg-white/95 backdrop-blur-sm rounded-xl shadow-lg p-8">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img
          src="https://upload.wikimedia.org/wikipedia/id/9/99/LOGO-UNWIKU-WARNA-BARU.png"
          alt="Logo UNWIKU"
          class="h-16 w-16 object-contain"
        />
      </div>

      <h1 class="text-2xl font-bold text-gray-800 mb-1 text-center">
        Login E-SPMI
      </h1>
      <p class="text-sm text-gray-500 mb-6 text-center">
        Masuk untuk melanjutkan ke sistem
      </p>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="username"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center text-sm text-gray-600 select-none">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2"
            />
            Ingat saya
          </label>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white py-2.5 rounded-md font-medium hover:bg-blue-700 transition"
        >
          Masuk
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'LoginPage' });

import { reactive } from 'vue';

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

function handleLogin() {
  console.log('Login submitted:', form);
}
</script>