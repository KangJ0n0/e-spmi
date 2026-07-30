<template>
  <div class="min-h-screen flex bg-gray-50 font-poppins">
    <!-- ============ SIDEBAR ============ -->
    <aside
      class="fixed lg:static inset-y-0 left-0 z-40 flex flex-col bg-[#0F2A4A] text-white transition-all duration-300"
      :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        collapsed ? 'w-20' : 'w-64',
      ]"
    >
      <!-- Brand -->
      <div class="h-20 flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
        <img
          src="https://upload.wikimedia.org/wikipedia/id/9/99/LOGO-UNWIKU-WARNA-BARU.png"
          alt="Logo UNWIKU"
          class="h-9 w-9 object-contain shrink-0"
        />
        <div v-if="!collapsed" class="leading-none overflow-hidden">
          <p class="font-bold text-sm tracking-wide">LPMU</p>
          <p class="text-white/50 text-[10px] tracking-[0.2em] mt-0.5">UNWIKU</p>
        </div>
      </div>

      <!-- Label peran -->
      <div v-if="!collapsed" class="px-5 py-4">
        <span
          class="inline-flex items-center gap-1.5 text-[10px] font-semibold tracking-[0.1em] uppercase px-2.5 py-1 rounded-full bg-[#C9A227]/15 text-[#C9A227]"
        >
          <span class="h-1.5 w-1.5 rounded-full bg-[#C9A227]"></span>
          {{ roleLabel }}
        </span>
      </div>

      <!-- Menu -->
      <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in sidebarMenu"
          :key="item.to"
          :to="item.to"
          class="sidebar-link"
          active-class="sidebar-link-active"
          :title="collapsed ? item.label : ''"
        >
          <component :is="iconFor(item.icon)" class="h-5 w-5 shrink-0" />
          <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Toggle collapse (desktop only) -->
      <button
        @click="collapsed = !collapsed"
        class="hidden lg:flex items-center justify-center h-11 border-t border-white/10 text-white/50 hover:text-white hover:bg-white/5 transition-colors"
      >
        <svg
          class="h-4 w-4 transition-transform duration-300"
          :class="{ 'rotate-180': collapsed }"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>
    </aside>

    <!-- Overlay saat sidebar mobile terbuka -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/40 z-30 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- ============ AREA KONTEN ============ -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar -->
      <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-4">
          <!-- Hamburger mobile -->
          <button
            @click="sidebarOpen = true"
            class="lg:hidden text-gray-500 hover:text-[#0F2A4A] p-1.5 -ml-1.5"
            aria-label="Buka menu"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div>
            <h1 class="text-lg font-bold text-gray-800">{{ pageTitle }}</h1>
            <p class="text-xs text-gray-400 mt-0.5">{{ todayLabel }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Avatar + nama -->
          <div class="hidden sm:flex items-center gap-2.5 pr-3 border-r border-gray-200">
            <div class="h-9 w-9 rounded-full bg-[#0F2A4A]/10 flex items-center justify-center text-[#0F2A4A] font-semibold text-sm">
              {{ userInitial }}
            </div>
            <div class="leading-tight">
              <p class="text-sm font-medium text-gray-800">{{ userName }}</p>
              <p class="text-xs text-gray-400">{{ roleLabel }}</p>
            </div>
          </div>

          <button
            @click="handleLogout"
            :disabled="loggingOut"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-red-600 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors disabled:opacity-50"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="hidden sm:inline">{{ loggingOut ? 'Keluar...' : 'Logout' }}</span>
          </button>
        </div>
      </header>

      <!-- Konten halaman -->
      <main class="flex-1 p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'MainLayout' });

import { ref, computed, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import axiosClient from '@/axios';
import useSidebar from '@/composable/useSidebar';

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false); // drawer mobile
const collapsed = ref(false);   // sidebar ramping di desktop
const loggingOut = ref(false);

// Semua data role/menu/user diambil dari composable, MainLayout fokus ke tampilan saja
const { roleLabel, sidebarMenu, userName, userInitial } = useSidebar();

const pageTitle = computed(() => route.meta?.title || 'Dashboard');

const todayLabel = computed(() =>
  new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
);

// ---- Logout ----
async function handleLogout() {
  loggingOut.value = true;
  try {
    await axiosClient.post('/logout');
  } catch (error) {
    console.warn('Logout request gagal, tetap membersihkan sesi lokal:', error);
  } finally {
    localStorage.removeItem('token');
    toast.success('Berhasil logout');
    loggingOut.value = false;
    router.push('/login');
  }
}

// ---- Ikon sidebar (inline SVG via render function, tanpa dependency ikon eksternal) ----
const icons = {
  home: 'M3 9.5L12 4l9 5.5M4.5 10.5V19a1 1 0 001 1h4v-5h5v5h4a1 1 0 001-1v-8.5',
  calendar: 'M8 7V3m8 4V3M3.5 11h17M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z',
  users: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 10-8 0 4 4 0 008 0z',
  user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z',
  note: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  file: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  upload: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3',
  search: 'M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z',
  check: 'M9 12.75l2.25 2.25L15 8.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

function iconFor(name) {
  const path = icons[name] || icons.file;
  return h(
    'svg',
    { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8' },
    [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: path })]
  );
}
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.85rem;
  border-radius: 0.65rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.65);
  transition: background-color 0.15s ease, color 0.15s ease;
}
.sidebar-link:hover {
  background-color: rgba(255, 255, 255, 0.06);
  color: #fff;
}
.sidebar-link-active {
  background-color: rgba(201, 162, 39, 0.15);
  color: #fff;
  font-weight: 600;
  box-shadow: inset 3px 0 0 #C9A227;
}

@media (prefers-reduced-motion: reduce) {
  aside,
  .sidebar-link {
    transition: none !important;
  }
}
</style>