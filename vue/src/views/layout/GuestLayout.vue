<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-20 flex items-center justify-between px-6 md:px-12 py-4 bg-blue-700/90 backdrop-blur-sm">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center text-blue-700 font-bold">
          ES
        </div>
        <span class="text-white font-semibold text-lg hidden sm:block">E-SPMI</span>
      </div>

      <ul class="hidden md:flex items-center gap-8 text-white text-sm font-medium">
        <li><router-link to="/" class="hover:text-blue-200 transition">Beranda</router-link></li>
        <li><router-link to="/spmi" class="hover:text-blue-200 transition">SPMI</router-link></li>
        <li><router-link to="/spme" class="hover:text-blue-200 transition">SPME</router-link></li>
        <li><router-link to="/kuisioner" class="hover:text-blue-200 transition">Kuisioner</router-link></li>
      </ul>

      <router-link
        to="/login"
        class="bg-white text-blue-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-50 transition"
      >
        Masuk
      </router-link>
    </nav>

    <!-- Konten halaman -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-blue-900 text-blue-100 text-center text-sm py-6">
      &copy; {{ new Date().getFullYear() }} E-SPMI. Seluruh hak cipta dilindungi.
    </footer>
  </div>
</template>

<script setup>
defineOptions({ name: 'GuestLayout' });
</script>