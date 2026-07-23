<template>
  <div class="min-h-screen bg-white">
    <!-- Navbar -->
    <nav class="absolute top-0 left-0 right-0 z-20 flex items-center justify-between px-6 md:px-16 py-6">
      <div class="flex items-center gap-3">
        <img
          src="https://upload.wikimedia.org/wikipedia/id/9/99/LOGO-UNWIKU-WARNA-BARU.png"
          alt="Logo UNWIKU"
          class="h-12 w-12 object-contain"
        />
        <span class="text-white font-bold text-xl hidden sm:block tracking-wide">LPMU UNWIKU</span>
      </div>

      <ul class="hidden md:flex items-center gap-10 text-white text-base font-medium">
        <li><a href="/" class="hover:text-blue-200 transition">Beranda</a></li>
        <li><a href="/profil" class="hover:text-blue-200 transition">Profil</a></li>
        <li><a href="#spmi" class="hover:text-blue-200 transition">SPMI</a></li>
        <li><a href="#spme" class="hover:text-blue-200 transition">SPME</a></li>
        <li><a href="#espmi" class="hover:text-blue-200 transition">E-SPMI</a></li>
        <li><a href="#kuisioner" class="hover:text-blue-200 transition">Kuisioner</a></li>
      </ul>

      <a
        href="/login"
        class="inline-flex items-center gap-1.5 text-white/80 hover:text-white text-sm font-medium border border-white/40 hover:border-white/70 px-4 py-1.5 rounded-full transition-all duration-200"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" />
        </svg>
        Masuk
      </a>
    </nav>

    <!-- Hero Section -->
    <section
      id="beranda"
      class="relative h-[420px] md:h-[500px] bg-cover bg-center flex items-end"
      style="background-image: url('https://unwiku.ac.id/wp-content/uploads/2025/02/Drone-luas-minimize.jpg')"
    >
      <div class="absolute inset-0 bg-gradient-to-t from-blue-900/70 via-blue-900/30 to-blue-900/50"></div>
    </section>

    <!-- Tagline Section -->
    <section class="text-center max-w-2xl mx-auto px-6 py-16">
      <p class="text-blue-600 font-semibold text-sm mb-2 tracking-wide">
        Lembaga
      </p>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">
        Penjaminan Mutu Universitas
      </h1>
      <p class="text-gray-500 leading-relaxed">
        Lembaga Penjaminan Mutu Universitas (LPMU) Universitas Wijayakusuma
        Purwokerto adalah unit resmi yang bertugas memastikan mutu akademik
        dan non-akademik universitas berjalan secara konsisten dan terpadu,
        melalui sistem SPMI, SPME, dan E-SPMI.
      </p>
    </section>

    <!-- Fitur / Kualitas Mutu Section -->
    <section class="relative bg-blue-700 pt-16 pb-20 px-6">
      <div
        class="absolute -top-8 left-0 right-0 h-16 bg-white"
        style="border-radius: 0 0 50% 50% / 0 0 100% 100%"
      ></div>

      <h2 class="relative text-center text-white text-xl md:text-2xl font-bold mb-10">
        Kualitas Mutu Yang Dijaga Oleh LPMU
      </h2>

      <div class="relative max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-8 text-center shadow-md">
          <div class="flex justify-center mb-4">
            <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 4l9 5.5M4.5 10.5V19a1 1 0 001 1h4v-5h5v5h4a1 1 0 001-1v-8.5" />
            </svg>
          </div>
          <h3 class="font-bold text-gray-800 mb-2">Sarana dan Prasarana</h3>
          <p class="text-sm text-gray-500 leading-relaxed">
            LPMU menjaga mutu sarana dan prasarana melalui fasilitas
            pembelajaran yang memadai serta lingkungan kampus yang mendukung
            mahasiswa.
          </p>
        </div>

        <div class="bg-white rounded-2xl p-8 text-center shadow-md">
          <div class="flex justify-center mb-4">
            <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-9-9v4c0 1.1 4 3 9 3s9-1.9 9-3v-4" />
            </svg>
          </div>
          <h3 class="font-bold text-gray-800 mb-2">Mahasiswa dan Alumni</h3>
          <p class="text-sm text-gray-500 leading-relaxed">
            LPMU memastikan mutu pendidikan dengan rekrutmen yang transparan
            dan kualitas lulusan yang sesuai kebutuhan dunia kerja.
          </p>
        </div>

        <div class="bg-white rounded-2xl p-8 text-center shadow-md">
          <div class="flex justify-center mb-4">
            <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 10-8 0 4 4 0 008 0z" />
            </svg>
          </div>
          <h3 class="font-bold text-gray-800 mb-2">Tata Kelola</h3>
          <p class="text-sm text-gray-500 leading-relaxed">
            LPMU menjaga mutu tata kelola melalui kepemimpinan, perencanaan
            strategis, serta pengelolaan keuangan yang transparan dan efisien.
          </p>
        </div>
      </div>
    </section>

    <!-- Motto LPMU -->
    <section class="max-w-5xl mx-auto px-6 py-16">
      <h2 class="text-center text-xl md:text-2xl font-bold text-gray-800 mb-2">
        Motto LPMU
      </h2>
      <p class="text-center text-gray-500 mb-10">
        Nilai yang menjadi landasan LPMU dalam menjaga mutu universitas
      </p>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
          <p class="text-2xl font-bold text-blue-700 mb-1">Mandiri</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
          <p class="text-2xl font-bold text-blue-700 mb-1">Inovatif</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
          <p class="text-2xl font-bold text-blue-700 mb-1">Kompetitif</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
          <p class="text-2xl font-bold text-blue-700 mb-1">Berjiwa Pancasila</p>
        </div>
      </div>
    </section>

    <!-- Kegiatan Terbaru Section -->
    <section id="galeri" class="max-w-3xl mx-auto px-6 py-16">
      <h2 class="text-center text-xl md:text-2xl font-bold text-gray-800 mb-10">
        Kegiatan LPMU Terbaru
      </h2>

      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-100 flex items-center justify-center h-64">
          <svg class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16l5-5 4 4 5-6 4 5M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z" />
          </svg>
        </div>
        <div class="p-6">
          <p class="text-xs text-gray-400 mb-1">04-09-2025</p>
          <h3 class="text-blue-700 font-bold mb-2">
            Website LPMU Hadir dengan Tampilan Baru
          </h3>
          <p class="text-sm text-gray-500">
            Website LPMU hadir dengan tampilan baru yang lebih modern dan sederhana.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-blue-100 text-center text-sm py-6">
      &copy; {{ new Date().getFullYear() }} LPMU UNWIKU. Seluruh hak cipta dilindungi.
    </footer>
  </div>
</template>

<script setup>
defineOptions({ name: 'LandingPage' });
</script>