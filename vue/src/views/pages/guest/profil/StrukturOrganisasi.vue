<template>
  <div class="lpmu-page bg-white">
    <!-- ============ BREADCRUMB ============ -->
    <div class="max-w-6xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[var(--navy)] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">Profil</span>
      <span>/</span>
      <span class="text-[var(--navy)] font-medium">Struktur Organisasi</span>
    </div>

    <!-- ============ HERO ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-16">
      <div class="text-center">
        <p
          class="text-[11px] md:text-xs font-bold tracking-[0.25em] text-[var(--gold)] uppercase mb-3"
        >
          Struktur Organisasi
        </p>
        <h1 class="font-poppins text-3xl md:text-4xl font-bold text-[var(--navy)]">
          Lembaga Penjaminan Mutu
        </h1>
      </div>
    </section>

    <!-- ============ LEVEL 1: KETUA ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-6">
      <div class="flex justify-center">
        <PersonCardComponent v-if="pimpinan" variant="lead" v-bind="pimpinan" />
      </div>
    </section>

    <!-- ============ KONEKTOR: KETUA -> KOORDINATOR (bagan organisasi) ============ -->
    <div class="max-w-6xl mx-auto px-6 flex flex-col items-center">
      <!-- garis pendek dari Ketua turun ke label -->
      <div class="w-px h-6 connector-line"></div>

      <p class="my-2 text-xs font-semibold tracking-[0.15em] text-gray-400 uppercase">
        Koordinator Bidang
      </p>

      <!-- garis pendek dari label turun ke titik cabang -->
      <div class="w-px h-6 connector-line"></div>

      <!-- Desktop: cabang ke 3 kartu -->
      <svg
        v-if="koordinator.length"
        class="hidden md:block w-full h-8"
        viewBox="0 0 600 32"
        preserveAspectRatio="none"
        role="img"
        aria-label="Garis struktur dari Ketua LPMU bercabang ke tiga koordinator bidang"
      >
        <line x1="100" y1="0" x2="500" y2="0" class="org-line" vector-effect="non-scaling-stroke" />
        <line
          x1="100"
          y1="0"
          x2="100"
          y2="32"
          class="org-line"
          vector-effect="non-scaling-stroke"
        />
        <line
          x1="300"
          y1="0"
          x2="300"
          y2="32"
          class="org-line"
          vector-effect="non-scaling-stroke"
        />
        <line
          x1="500"
          y1="0"
          x2="500"
          y2="32"
          class="org-line"
          vector-effect="non-scaling-stroke"
        />
      </svg>

      <!-- Mobile: 1 kolom, langsung turun ke kartu pertama -->
      <div class="md:hidden w-px h-6 connector-line"></div>
    </div>

    <!-- ============ LEVEL 2: KOORDINATOR BIDANG ============ -->
    <section class="max-w-6xl mx-auto px-6 pt-0 pb-24">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <PersonCardComponent
          v-for="item in koordinator"
          :key="item.nama"
          variant="coordinator"
          v-bind="item"
        />
      </div>
    </section>

    <!-- ============ CALLOUT: TIM AUDITOR (AMI) ============ -->
    <section class="bg-[var(--navy)] py-16 px-6 relative overflow-hidden">
      <div
        class="absolute top-0 right-0 w-72 h-72 bg-[var(--gold)] rounded-full blur-3xl opacity-10 -translate-y-1/2 translate-x-1/3 pointer-events-none"
      ></div>

      <div
        class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 md:gap-14 items-center relative z-10"
      >
        <div class="flex items-baseline gap-1 shrink-0">
          <span
            class="font-poppins font-extrabold text-[var(--gold)] text-5xl md:text-6xl leading-none tracking-tight"
          >
            Tim AMI
          </span>
        </div>
        <div class="border-t md:border-t-0 md:border-l border-white/15 pt-6 md:pt-0 md:pl-14">
          <p class="text-[11px] font-bold tracking-[0.2em] text-white/50 uppercase mb-2">
            Gugus Tugas Khusus
          </p>
          <p class="text-white/85 leading-relaxed max-w-2xl text-[1.1rem]">
            Dalam pelaksanaan kegiatan
            <router-link
              to="/spmi/evaluasi"
              class="text-white font-semibold hover:text-[var(--gold)] transition-colors"
            >
              Audit Mutu Internal </router-link
            >, LPMU dapat membentuk <span class="text-[var(--gold)] font-medium">Tim Auditor</span>
            secara independen untuk melaksanakan, mengawal, dan memastikan efektivitas siklus SPMI
            di seluruh unit Universitas.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axiosClient from '@/axios'
import PersonCardComponent from '@/components/PersonCardComponent.vue'

defineOptions({ name: 'StrukturOrganisasiPage' })

// Dulu nama/jabatan/foto di sini hardcode langsung di file ini - begitu Admin ganti data di
// menu Struktur Anggota, halaman publik ini nggak ikut berubah sama sekali. Sekarang ditarik
// dari endpoint publik /public/struktur-organisasi (data sama yang Admin kelola).
const pimpinan = ref(null)
const koordinator = ref([])

const mapFoto = (item) => ({
  nama: item.nama,
  jabatan: item.jabatan,
  foto: item.foto_url || '',
})

const fetchStruktur = async () => {
  try {
    const res = await axiosClient.get('/public/struktur-organisasi')
    pimpinan.value = res.data?.pimpinan ? mapFoto(res.data.pimpinan) : null
    koordinator.value = (res.data?.koordinator || []).map(mapFoto)
  } catch (error) {
    console.error('Gagal mengambil data struktur organisasi:', error)
  }
}

onMounted(() => {
  fetchStruktur()
})
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.lpmu-page {
  --navy: #0f2a4a;
  --gold: #c9a227;
}

.connector-line {
  background-color: color-mix(in srgb, var(--navy) 15%, transparent);
}

.org-line {
  stroke: var(--navy);
  stroke-width: 2;
  opacity: 0.3;
}
</style>
