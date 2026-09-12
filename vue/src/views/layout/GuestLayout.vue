<template>
  <div class="min-h-screen flex flex-col bg-white font-poppins">
    <!-- ============ NAVBAR ============ -->
    <nav
      class="fixed top-0 left-0 right-0 z-40 transition-all duration-300"
      :class="
        isScrolled
          ? 'bg-[#0F2A4A] shadow-[0_4px_20px_rgba(15,42,74,0.25)]'
          : 'bg-slate-900/45 backdrop-blur-md'
      "
    >
      <div class="flex items-center justify-between px-6 md:px-12 h-20">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 shrink-0">
          <img
            src="https://upload.wikimedia.org/wikipedia/id/9/99/LOGO-UNWIKU-WARNA-BARU.png"
            alt="Logo UNWIKU"
            class="h-11 w-11 object-contain"
          />
          <div class="hidden sm:flex flex-col leading-none">
            <span class="text-white font-bold text-xl tracking-wide drop-shadow-sm">LPMU</span>
            <span
              class="text-white/75 text-[11px] tracking-[0.2em] font-semibold mt-0.5 drop-shadow-sm"
              >UNIVERSITAS WIJAYAKUSUMA</span
            >
          </div>
        </router-link>

        <!-- Menu Desktop -->
        <ul class="hidden lg:flex items-center gap-1 text-white text-sm">
          <li>
            <router-link to="/" class="nav-link" active-class="nav-link-active">
              Beranda
            </router-link>
          </li>

          <li
            v-for="menu in dropdownMenus"
            :key="menu.label"
            class="relative"
            @mouseenter="openMenu = menu.label"
            @mouseleave="openMenu = null"
          >
            <button
              class="nav-link flex items-center gap-1.5"
              :class="{ 'nav-link-active': openMenu === menu.label }"
              @click="openMenu = openMenu === menu.label ? null : menu.label"
            >
              {{ menu.label }}
              <svg
                class="h-3 w-3 transition-transform duration-200"
                :class="{ 'rotate-180': openMenu === menu.label }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!--
              Dropdown Panel — PENTING: pakai top-full + padding-top (BUKAN margin-top).
              Ini membuat area hover menyambung tanpa celah dari tombol ke panel,
              jadi mouse tidak pernah "keluar" dari hitbox saat bergerak turun.
            -->
            <transition name="dropdown">
              <div
                v-if="openMenu === menu.label"
                class="absolute left-1/2 -translate-x-1/2 top-full pt-4"
                :class="menu.cycle ? 'w-80' : 'w-64'"
              >
                <!-- Anak panah kecil penunjuk -->
                <div
                  class="absolute top-2.5 left-1/2 -translate-x-1/2 h-3 w-3 rotate-45 bg-white border-l border-t border-gray-100"
                ></div>

                <div
                  class="relative bg-white rounded-2xl shadow-[0_20px_50px_-12px_rgba(15,42,74,0.35)] border border-gray-100 overflow-hidden"
                >
                  <p
                    class="px-5 pt-4 pb-2 text-[10px] font-semibold tracking-[0.15em] text-gray-400 uppercase"
                  >
                    {{ menu.eyebrow }}
                  </p>

                  <!-- Versi siklus (khusus SPMI/PPEPP) -->
                  <div v-if="menu.cycle" class="px-2 pb-3">
                    <component
                      :is="item.to ? 'router-link' : 'a'"
                      v-for="(item, idx) in menu.items"
                      :key="item.label"
                      :to="item.to"
                      :href="item.href"
                      :target="item.href ? '_blank' : undefined"
                      :rel="item.href ? 'noopener noreferrer' : undefined"
                      class="group flex items-center gap-3 mx-1 px-3.5 py-2.5 rounded-xl hover:bg-[#0F2A4A]/[0.04] transition-colors"
                      @click="openMenu = null"
                    >
                      <span
                        class="flex items-center justify-center h-6 w-6 rounded-full text-[11px] font-bold shrink-0 border-[1.5px] border-[#C9A227]/40 text-[#0F2A4A] group-hover:bg-[#C9A227] group-hover:border-[#C9A227] group-hover:text-white transition-colors"
                      >
                        {{ idx + 1 }}
                      </span>
                      <span
                        class="text-sm text-gray-700 group-hover:text-[#0F2A4A] font-medium transition-colors"
                      >
                        {{ item.label }}
                      </span>
                    </component>
                  </div>

                  <!-- Versi list biasa (Profil, SPME, Kuisioner) -->
                  <div v-else class="px-2 pb-2">
                    <component
                      :is="item.to ? 'router-link' : 'a'"
                      v-for="item in menu.items"
                      :key="item.label"
                      :to="item.to"
                      :href="item.href"
                      :target="item.href ? '_blank' : undefined"
                      :rel="item.href ? 'noopener noreferrer' : undefined"
                      class="flex items-center justify-between mx-1 px-3.5 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-[#0F2A4A]/[0.04] hover:text-[#0F2A4A] font-medium transition-colors"
                      @click="openMenu = null"
                    >
                      {{ item.label }}
                      <svg
                        v-if="item.href"
                        class="h-3 w-3 text-gray-300 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M17.25 6.75L6.75 17.25M6.75 6.75h10.5v10.5"
                        />
                      </svg>
                    </component>
                  </div>
                </div>
              </div>
            </transition>
          </li>

          <!-- E-SPMI: langsung ke /login, tanpa dropdown. Ini satu-satunya jalur masuk ke sistem. -->
          <li>
            <router-link to="/login" class="nav-link" active-class="nav-link-active">
              E-SPMI
            </router-link>
          </li>
        </ul>

        <!-- Hamburger mobile -->
        <button
          @click="mobileOpen = !mobileOpen"
          class="lg:hidden text-white p-2 -mr-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#C9A227] rounded-lg"
          aria-label="Buka menu navigasi"
        >
          <svg
            v-if="!mobileOpen"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg
            v-else
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- ============ MENU MOBILE ============ -->
      <transition name="fade">
        <div
          v-if="mobileOpen"
          class="lg:hidden bg-[#0F2A4A] max-h-[calc(100vh-5rem)] overflow-y-auto border-t border-white/10"
        >
          <router-link
            to="/"
            class="block px-6 py-3.5 text-white text-sm font-medium border-b border-white/[0.08]"
            @click="mobileOpen = false"
          >
            Beranda
          </router-link>

          <div v-for="menu in dropdownMenus" :key="menu.label" class="border-b border-white/[0.08]">
            <button
              class="w-full flex items-center justify-between px-6 py-3.5 text-white text-sm font-medium"
              @click="openMobileMenu = openMobileMenu === menu.label ? null : menu.label"
            >
              {{ menu.label }}
              <svg
                class="h-4 w-4 text-white/50 transition-transform duration-200"
                :class="{ 'rotate-180': openMobileMenu === menu.label }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div v-if="openMobileMenu === menu.label" class="bg-black/20 pb-2">
              <component
                :is="item.to ? 'router-link' : 'a'"
                v-for="(item, idx) in menu.items"
                :key="item.label"
                :to="item.to"
                :href="item.href"
                :target="item.href ? '_blank' : undefined"
                :rel="item.href ? 'noopener noreferrer' : undefined"
                class="flex items-center gap-3 px-6 py-2.5 text-sm text-blue-100 hover:text-white transition-colors"
                @click="mobileOpen = false"
              >
                <span
                  v-if="menu.cycle"
                  class="flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-bold border border-[#C9A227]/50 text-[#C9A227] shrink-0"
                >
                  {{ idx + 1 }}
                </span>
                {{ item.label }}
              </component>
            </div>
          </div>

          <!-- E-SPMI: satu-satunya jalur ke /login, sekaligus penutup daftar menu mobile -->
          <router-link
            to="/login"
            class="block px-6 py-3.5 text-white text-sm font-semibold"
            @click="mobileOpen = false"
          >
            E-SPMI
          </router-link>
        </div>
      </transition>
    </nav>

    <!-- ============ KONTEN ============ -->
    <main class="flex-1 pt-20">
      <router-view />
    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="bg-[#081B33] text-blue-100">
      <div
        class="max-w-6xl mx-auto px-6 py-14 grid grid-cols-1 md:grid-cols-[1.3fr_1fr_1fr] gap-10 text-sm"
      >
        <div>
          <div class="flex items-center gap-3 mb-4">
            <img
              src="https://upload.wikimedia.org/wikipedia/id/9/99/LOGO-UNWIKU-WARNA-BARU.png"
              alt="Logo UNWIKU"
              class="h-10 w-10 object-contain"
            />
            <div class="leading-none">
              <p class="text-white font-bold text-base tracking-wide">LPMU</p>
              <p class="text-white/60 text-xs tracking-[0.2em] mt-0.5">UNIVERSITAS WIJAYAKUSUMA</p>
            </div>
          </div>
          <p class="text-blue-200/80 leading-relaxed max-w-xs">
            Lembaga Penjaminan Mutu Universitas Wijayakusuma Purwokerto — menjaga siklus Penetapan,
            Pelaksanaan, Evaluasi, Pengendalian, dan Peningkatan mutu secara berkelanjutan.
          </p>
        </div>

        <div>
          <p class="text-white font-semibold mb-4 text-xs tracking-[0.1em] uppercase">Tautan</p>
          <ul class="space-y-2.5 text-blue-200/80">
            <li>
              <router-link to="/profil/visi" class="hover:text-[#C9A227] transition-colors"
                >Profil</router-link
              >
            </li>
            <li>
              <router-link to="/spmi/penetapan" class="hover:text-[#C9A227] transition-colors"
                >SPMI</router-link
              >
            </li>
            <li>
              <router-link to="/login" class="hover:text-[#C9A227] transition-colors"
                >E-SPMI</router-link
              >
            </li>
          </ul>
        </div>

        <div>
          <p class="text-white font-semibold mb-4 text-xs tracking-[0.1em] uppercase">Kontak</p>
          <ul class="space-y-2.5 text-blue-200/80">
            <li>Email: info@unwiku.ac.id</li>
            <li>Telp: (0281) 6439889</li>
            <li>
              Jl. Raya Beji Karangsalam No.25, Dusun III, Karangsalam Kidul, Kec. Kedungbanteng,
              Kabupaten Banyumas, Jawa Tengah 53152
            </li>
          </ul>
        </div>
      </div>

      <div class="border-t border-white/[0.08] text-center text-xs text-blue-200/60 py-5">
        &copy; {{ new Date().getFullYear() }} LPMU UNWIKU. Seluruh hak cipta dilindungi.
      </div>
    </footer>
  </div>
</template>

<script setup>
defineOptions({ name: 'GuestLayout' })

import { ref, onMounted, onUnmounted } from 'vue'

const mobileOpen = ref(false)
const openMenu = ref(null)
const openMobileMenu = ref(null)
const isScrolled = ref(false)

function handleScroll() {
  isScrolled.value = window.scrollY > 12
}

onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

// item.to    → halaman internal (router-link)
// item.href  → link eksternal, otomatis buka tab baru + ikon panah (↗)
// E-SPMI sengaja tidak ada di sini karena langsung diarahkan ke /login (lihat <li> terpisah di navbar)
const dropdownMenus = [
  {
    label: 'Profil',
    eyebrow: 'Tentang Lembaga',
    cycle: false,
    items: [
      { label: 'Visi LPMU UNWIKU', to: '/profil/visi' },
      { label: 'Struktur Organisasi', to: '/profil/struktur-organisasi' },
      { label: 'Tugas dan Fungsi', to: '/profil/tugas-fungsi' },
      { label: 'UPMF', to: '/profil/upmf' },
    ],
  },
  {
    label: 'SPMI',
    eyebrow: 'Siklus PPEPP',
    cycle: true,
    items: [
      { label: 'Penetapan', to: '/spmi/penetapan' },
      { label: 'Pelaksanaan', to: '/spmi/pelaksanaan' },
      { label: 'Evaluasi', to: '/login' },
      { label: 'Pengendalian', to: '/spmi/pengendalian' },
      { label: 'Peningkatan', to: '/spmi/peningkatan' },
    ],
  },
  {
    label: 'SPME',
    eyebrow: 'Lembaga Akreditasi',
    cycle: false,
    // TODO: ganti href dengan URL resmi masing-masing lembaga akreditasi
    items: [
      { label: 'BAN-PT', href: 'https://www.banpt.or.id' },
      { label: 'LAMSPAK', href: 'https://www.lamspak.id' },
      { label: 'LAMEMBA', href: 'https://lamemba.or.id' },
      { label: 'LAMTEKNIK', href: 'https://lamteknik.or.id' },
      { label: 'LAMPTIP', href: 'https://lamptip.or.id' },
    ],
  },
  {
    label: 'Kuisioner',
    eyebrow: 'Survei Kepuasan',
    cycle: false,
    // TODO: ganti href dengan URL form kuisioner asli (Google Form/lainnya)
    items: [
      { label: 'Kepuasan Mahasiswa', href: '/kuisioner/kepuasan-mahasiswa' },
      { label: 'Kepuasan Dosen', href: '/kuisioner/kepuasan-dosen' },
      { label: 'Kepuasan Tendik', href: '/kuisioner/kepuasan-tendik' },
      { label: 'Kepuasan Mitra Pendidikan', href: '/kuisioner/kepuasan-mitra-pendidikan' },
      { label: 'Kepuasan Mitra Penelitian', href: '/kuisioner/kepuasan-mitra-penelitian' },
      { label: 'Kepuasan Mitra Pengabdian', href: '/kuisioner/kepuasan-mitra-pengabdian' },
      { label: 'Perilaku Entrepreneurship', href: 'https://forms.gle/xxxxx' },
      { label: 'Perilaku Berjiwa Pancasila', href: 'https://forms.gle/xxxxx' },
    ],
  },
]
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.nav-link {
  position: relative;
  padding: 0.5rem 0.9rem;
  border-radius: 9999px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
  transition:
    color 0.2s ease,
    background-color 0.2s ease;
}
.nav-link:hover {
  color: #fff;
  background-color: rgba(255, 255, 255, 0.12);
}
.nav-link:focus-visible {
  outline: 2px solid #c9a227;
  outline-offset: 2px;
}
.nav-link-active {
  background-color: rgba(201, 162, 39, 0.18);
  color: #fff;
}
.nav-link-active::after {
  content: '';
  position: absolute;
  left: 0.9rem;
  right: 0.9rem;
  bottom: 0.15rem;
  height: 2px;
  background-color: #c9a227;
  border-radius: 2px;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 0.18s ease,
    transform 0.18s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translate(-50%, -8px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .nav-link,
  .dropdown-enter-active,
  .dropdown-leave-active,
  .fade-enter-active,
  .fade-leave-active {
    transition: none !important;
  }
}
</style>
