<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Pilih Instrumen untuk Jadwal Ini</h1>
        <p class="text-sm text-gray-500">
          Centang butir dari Instrumen yang mau dikirim ke Auditee sesuai jadwal audit ini.
        </p>
      </div>
      <router-link
        :to="{ path: '/auditor/jadwal-auditor' }"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- Pertanyaan yang SUDAH dipilih untuk jadwal ini -->
    <div class="mb-8">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">
        Sudah Dipilih ({{ listPertanyaan.length }})
      </h2>
      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
                Butir Pertanyaan
              </th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                Status Jawaban
              </th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="listPertanyaan.length === 0">
              <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                Belum ada pertanyaan yang dipilih untuk jadwal ini.
              </td>
            </tr>
            <tr v-for="item in listPertanyaan" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
                {{ item.pertanyaan.butir_pertanyaan }}
              </td>
              <td class="px-4 py-3 text-center text-sm">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="
                    item.status_jawaban === 'sudah'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-yellow-100 text-yellow-700'
                  "
                >
                  {{ item.status_jawaban === 'sudah' ? 'Sudah Dijawab' : 'Belum Dijawab' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-sm">
                <button
                  v-if="item.status_jawaban !== 'sudah'"
                  @click="hapusPertanyaan(item)"
                  class="text-red-600 hover:text-red-800 font-medium text-xs"
                >
                  Batalkan
                </button>
                <span v-else class="text-xs text-gray-400" title="Sudah ada jawaban, tidak bisa dibatalkan">
                  Terkunci
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Instrumen: pilih tambahan -->
    <div>
      <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
        <h2 class="text-sm font-semibold text-gray-700">Instrumen</h2>
        <div class="flex items-center gap-2">
          <!-- Filter Kategori (fitur baru 9 Sep 2026) - "Semua Kategori" = perilaku lama.
          Ikon panah dirapikan 10 Sep, bug yang sama kayak dropdown Kategori Instrumen di halaman
          Admin (lihat BankPertanyaan.vue) - appearance-none + ikon panah custom + pr-8. -->
          <div class="relative">
            <select
              v-model="kategoriFilter"
              class="appearance-none border border-gray-300 rounded-md pl-3 pr-8 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Semua Kategori</option>
              <option value="none">Tanpa Kategori</option>
              <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
            </select>
            <svg
              class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <input
            v-model="pencarian"
            type="text"
            placeholder="Cari pertanyaan / butir..."
            class="border border-gray-300 rounded-md px-3 py-1.5 text-sm w-72 focus:ring-blue-500 focus:border-blue-500"
          />
          <!-- Kirim langsung tanpa perlu centang manual (10 Sep 2026) - user minta opsi "kategori
          ini kirim semua ke Auditee". Kerja di atas hasil filter kategori + pencarian yang sedang
          aktif (bankPertanyaanTampil), bukan cuma yang sudah dicentang manual di selectedBaru. -->
          <button
            @click="kirimSemuaTampil"
            :disabled="soalBelumTerpilihTampil.length === 0 || isSubmittingSemua"
            class="bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 rounded-md text-sm font-medium disabled:opacity-50 whitespace-nowrap"
            title="Langsung kirim semua soal yang cocok filter kategori/pencarian ini ke Auditee, tanpa perlu centang manual"
          >
            {{
              isSubmittingSemua
                ? 'Mengirim...'
                : `Kirim Semua ${soalBelumTerpilihTampil.length || ''} Soal Kategori Ini`
            }}
          </button>
        </div>
      </div>

      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-10">
                <!-- Dulu checkbox polos tanpa keterangan sama sekali, dilaporkan "kurang jelas"
                (10 Sep). Ditambah title/tooltip - fungsinya centang manual semua baris yang
                tampil (masih bisa di-uncheck sebelum kirim), beda dari tombol "Kirim Semua ...
                Kategori Ini" di atas yang langsung kirim tanpa berhenti dulu. -->
                <input
                  type="checkbox"
                  title="Centang semua baris yang tampil di tabel ini (masih bisa di-uncheck sebelum kirim)"
                  class="h-5 w-5 rounded border-2 border-gray-400 bg-white accent-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  :checked="semuaTerpilihDiHalamanIni"
                  @change="toggleSemua($event.target.checked)"
                />
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                Pertanyaan / Pernyataan Standar
              </th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                Butir Pertanyaan
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isLoading">
              <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">Memuat...</td>
            </tr>
            <tr v-else-if="bankPertanyaanTampil.length === 0">
              <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                Tidak ada pertanyaan yang cocok.
              </td>
            </tr>
            <tr
              v-for="item in bankPertanyaanTampil"
              :key="item.id"
              class="hover:bg-gray-50"
              :class="{ 'bg-blue-50/50': sudahDipilih(item.id) }"
            >
              <td class="px-4 py-3 text-center">
                <input
                  type="checkbox"
                  class="h-5 w-5 rounded border-2 border-gray-400 bg-white accent-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer disabled:cursor-not-allowed disabled:opacity-70"
                  :checked="sudahDipilih(item.id) || selectedBaru.has(item.id)"
                  :disabled="sudahDipilih(item.id)"
                  @change="toggleSatu(item.id, $event.target.checked)"
                />
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
                {{ item.pertanyaan }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
                {{ item.butir_pertanyaan }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ selectedBaru.size }} pertanyaan baru dipilih</p>
        <button
          @click="kirimKeAuditee"
          :disabled="selectedBaru.size === 0 || isSubmitting"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-medium shadow-sm disabled:opacity-50"
        >
          {{ isSubmitting ? 'Mengirim...' : `Kirim ${selectedBaru.size || ''} Pertanyaan ke Auditee` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'

const route = useRoute()
const jadwalId = route.params.id

const bankPertanyaan = ref([])
const listPertanyaan = ref([]) // yang sudah dipilih untuk jadwal ini (list_pertanyaans + relasi pertanyaan)
const pencarian = ref('')
const isLoading = ref(false)
const isSubmitting = ref(false)
const isSubmittingSemua = ref(false) // tombol "Kirim Semua ... Kategori Ini" (10 Sep 2026)

// Kategori Instrumen (fitur baru 9 Sep 2026) - "all" = Semua Kategori (perilaku lama).
const kategoriList = ref([])
const kategoriFilter = ref('all')

// Set pertanyaan_id yang baru dicentang di sesi ini (belum dikirim)
const selectedBaru = reactive(new Set())

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const idYangSudahDipilih = computed(() => new Set(listPertanyaan.value.map((lp) => lp.pertanyaan_id)))
const sudahDipilih = (pertanyaanId) => idYangSudahDipilih.value.has(pertanyaanId)

const bankPertanyaanTampil = computed(() => {
  let hasil = bankPertanyaan.value

  // Filter kategori dulu (fitur baru 9 Sep 2026), baru pencarian teks - dua-duanya jalan
  // bersamaan di client, sama pola dengan pencarian yang sudah ada.
  if (kategoriFilter.value === 'none') {
    hasil = hasil.filter((item) => !item.kategori_instrumen)
  } else if (kategoriFilter.value !== 'all') {
    hasil = hasil.filter((item) => item.kategori_instrumen?.id === kategoriFilter.value)
  }

  const q = pencarian.value.trim().toLowerCase()
  if (!q) return hasil
  return hasil.filter(
    (item) =>
      item.pertanyaan?.toLowerCase().includes(q) || item.butir_pertanyaan?.toLowerCase().includes(q),
  )
})

const semuaTerpilihDiHalamanIni = computed(() => {
  const dipilihkan = bankPertanyaanTampil.value.filter((i) => !sudahDipilih(i.id))
  return dipilihkan.length > 0 && dipilihkan.every((i) => selectedBaru.has(i.id))
})

// Soal yang cocok filter kategori/pencarian aktif TAPI belum pernah dikirim ke jadwal ini -
// dasar buat tombol "Kirim Semua ... Kategori Ini" (10 Sep 2026), independen dari selectedBaru
// (centang manual) karena tombol ini langsung kirim tanpa perlu centang dulu.
const soalBelumTerpilihTampil = computed(() =>
  bankPertanyaanTampil.value.filter((i) => !sudahDipilih(i.id)),
)

const toggleSatu = (pertanyaanId, checked) => {
  if (checked) selectedBaru.add(pertanyaanId)
  else selectedBaru.delete(pertanyaanId)
}

const toggleSemua = (checked) => {
  bankPertanyaanTampil.value.forEach((item) => {
    if (sudahDipilih(item.id)) return
    if (checked) selectedBaru.add(item.id)
    else selectedBaru.delete(item.id)
  })
}

const fetchBankPertanyaan = async () => {
  const res = await axiosClient.get('/bank-pertanyaan')
  if (gagal(res)) return
  bankPertanyaan.value = res.data
}

const fetchKategoriList = async () => {
  const res = await axiosClient.get('/kategori-instrumen')
  if (gagal(res)) return
  kategoriList.value = res.data
}

const fetchListPertanyaan = async () => {
  const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
  if (gagal(res)) return
  listPertanyaan.value = res.data
}

const kirimKeAuditee = async () => {
  if (selectedBaru.size === 0) return
  isSubmitting.value = true
  try {
    const res = await axiosClient.post('/list-pertanyaan', {
      jadwal_id: jadwalId,
      pertanyaan_ids: Array.from(selectedBaru),
    })
    if (gagal(res)) return

    selectedBaru.clear()
    await fetchListPertanyaan()
  } finally {
    isSubmitting.value = false
  }
}

// Tombol "Kirim Semua ... Kategori Ini" (10 Sep 2026) - user eksplisit minta alur 1-tombol-
// langsung-kirim (dikonfirmasi lewat AskUserQuestion), BUKAN centang-semua-dulu-baru-kirim.
// Kirim SEMUA soal yang cocok filter kategori + pencarian aktif & belum pernah dikirim,
// terlepas dari selectedBaru (centang manual) - keduanya independen.
const kirimSemuaTampil = async () => {
  const target = soalBelumTerpilihTampil.value
  if (target.length === 0) return
  if (
    !confirm(
      `Kirim semua ${target.length} soal yang cocok filter ini ke Auditee sekarang? Aksi ini langsung terkirim, tidak perlu centang manual dulu.`,
    )
  )
    return

  isSubmittingSemua.value = true
  try {
    const res = await axiosClient.post('/list-pertanyaan', {
      jadwal_id: jadwalId,
      pertanyaan_ids: target.map((i) => i.id),
    })
    if (gagal(res)) return

    // Buang dari selectedBaru juga kalau kebetulan ada yang sempat dicentang manual duluan,
    // biar hitungan "X pertanyaan baru dipilih" di bawah tetap akurat.
    target.forEach((i) => selectedBaru.delete(i.id))
    await fetchListPertanyaan()
  } finally {
    isSubmittingSemua.value = false
  }
}

const hapusPertanyaan = async (item) => {
  if (item.status_jawaban === 'sudah') return
  if (!confirm('Batalkan pertanyaan ini dari jadwal? Auditee/Auditor tidak akan melihatnya lagi.')) return

  const res = await axiosClient.delete(`/list-pertanyaan/${item.id}`)
  if (gagal(res)) return

  await fetchListPertanyaan()
}

onMounted(async () => {
  isLoading.value = true
  await Promise.all([fetchBankPertanyaan(), fetchListPertanyaan(), fetchKategoriList()])
  isLoading.value = false
})
</script>
