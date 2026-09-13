<template>
  <ButtonComponent variant="primary" @click="buttonBack"> Kembali </ButtonComponent>

  <div>
    <form @submit.prevent="buttonSubmitForm">
      <div class="grid gap-6 mb-6 md:grid-cols-2">
        <div>
          <label for="nama_jadwal" class="block mb-2.5 text-sm font-medium text-heading"
            >Nama Jadwal</label
          >
          <input
            type="text"
            id="nama_jadwal"
            v-model="model.nama_jadwal"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            placeholder="Masukkan nama jadwal"
            required
          />
        </div>

        <div>
          <label for="area_audit" class="block mb-2.5 text-sm font-medium text-heading"
            >Area Audit</label
          >
          <input
            type="text"
            id="area_audit"
            v-model="model.area_audit"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            placeholder="Masukkan area audit"
            required
          />
        </div>

        <div>
          <label for="tanggal_awal" class="block mb-2.5 text-sm font-medium text-heading"
            >Tanggal Awal</label
          >
          <input
            type="date"
            id="tanggal_awal"
            v-model="model.tanggal_awal"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            required
          />
        </div>

        <div>
          <label for="tanggal_akhir" class="block mb-2.5 text-sm font-medium text-heading"
            >Tanggal Akhir</label
          >
          <input
            type="date"
            id="tanggal_akhir"
            v-model="model.tanggal_akhir"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            required
          />
        </div>

        <div>
          <label for="semester" class="block mb-2.5 text-sm font-medium text-heading"
            >Semester</label
          >
          <Multiselect
            id="semester"
            v-model="model.semester"
            :options="list_semester"
            :searchable="true"
            :close-on-select="true"
            :show-labels="false"
            placeholder="Pilih Semester"
            valueProp="id"
            label="name"
            track-by="name"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
          />
        </div>
      </div>

      <button
        type="submit"
        :disabled="isSubmittingForm"
        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isSubmittingForm ? 'Menyimpan...' : 'Submit' }}
      </button>
    </form>

    <!-- ============ PENUGASAN AUDITOR / AUDITEE ============ -->
    <!-- Hanya muncul saat EDIT, karena butuh jadwal_spmi_id yang sudah tersimpan. Dibungkus
    Transition (11 Sep 2026, permintaan user) biar panel ini muncul dengan animasi halus pas
    auto-lanjut dari mode create -> edit sehabis submit jadwal baru, bukan tiba-tiba nongol. -->
    <Transition name="panel-pop" appear>
    <div v-if="props.tipe === 'edit'" class="mt-10 pt-8 border-t border-default-medium">
      <h3 class="text-base font-semibold text-heading mb-1">Penugasan Auditor & Auditee</h3>
      <p class="text-sm text-body mb-6">
        Kelola dosen yang ditugaskan untuk jadwal
        <span class="font-medium text-heading">{{ model.nama_jadwal }}</span>
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kolom Auditor -->
        <div class="border border-default-medium rounded-base p-4">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-brand"></span>
              <p class="text-sm font-semibold text-heading">Auditor</p>
            </div>
            <button
              type="button"
              @click="openPickModal('auditor')"
              class="text-white bg-brand border border-transparent hover:bg-brand-strong shadow-xs font-medium rounded-base text-xs px-3 py-1.5"
            >
              + Tambah
            </button>
          </div>

          <p class="text-xs text-body mb-2">
            Klik ikon mahkota untuk menentukan Ketua Auditor - namanya yang dipakai di kolom
            tanda tangan & ditaruh paling atas saat dokumen dicetak.
          </p>
          <div class="space-y-2 min-h-[3rem]">
            <!-- Dulu nggak ada indikator apa pun di sini pas narik data auditor/auditee (dilaporkan
            user 11 Sep) - dari luar kelihatan freeze sesaat. -->
            <div v-if="loadingPenugasan" class="flex items-center gap-2 text-sm text-body py-2">
              <svg class="animate-spin h-4 w-4 text-brand" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Memuat data auditor...
            </div>
            <div
              v-for="item in auditorList"
              v-else
              :key="item.id"
              class="flex items-center justify-between px-3 py-2 rounded-base bg-neutral-secondary-medium"
            >
              <div class="flex items-center gap-2 min-w-0">
                <button
                  type="button"
                  @click="handleSetKetua(item)"
                  :title="item.is_ketua ? 'Ketua Auditor' : 'Jadikan Ketua Auditor'"
                  class="shrink-0"
                  :class="item.is_ketua ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400'"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5 16L3 6l4.5 3L10 4l2.5 5L17 6l-2 10H5z" />
                  </svg>
                </button>
                <span class="text-sm text-heading truncate">{{ item.nama_dosen }}</span>
                <span
                  v-if="item.is_ketua"
                  class="text-[10px] font-semibold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full shrink-0"
                >
                  Ketua
                </span>
              </div>
              <button
                @click="handleRemovePenugasan(item, 'auditor')"
                class="text-red-500 hover:text-red-700 shrink-0"
              >
                <svg
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <p v-if="!loadingPenugasan && auditorList.length === 0" class="text-sm text-body italic py-2">
              Belum ada auditor ditugaskan.
            </p>
          </div>
        </div>

        <!-- Kolom Auditee -->
        <div class="border border-default-medium rounded-base p-4">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full bg-heading"></span>
              <p class="text-sm font-semibold text-heading">Auditee</p>
            </div>
            <button
              type="button"
              @click="openPickModal('auditee')"
              class="text-white bg-heading border border-transparent hover:opacity-90 shadow-xs font-medium rounded-base text-xs px-3 py-1.5"
            >
              + Tambah
            </button>
          </div>

          <div class="space-y-2 min-h-[3rem]">
            <div v-if="loadingPenugasan" class="flex items-center gap-2 text-sm text-body py-2">
              <svg class="animate-spin h-4 w-4 text-heading" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Memuat data auditee...
            </div>
            <div
              v-for="item in auditeeList"
              v-else
              :key="item.id"
              class="flex items-center justify-between px-3 py-2 rounded-base bg-neutral-secondary-medium"
            >
              <span class="text-sm text-heading">{{ item.nama_dosen }}</span>
              <button
                @click="handleRemovePenugasan(item, 'auditee')"
                class="text-red-500 hover:text-red-700 shrink-0"
              >
                <svg
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <p v-if="!loadingPenugasan && auditeeList.length === 0" class="text-sm text-body italic py-2">
              Belum ada auditee ditugaskan.
            </p>
          </div>
        </div>
      </div>
    </div>
    </Transition>

    <!-- ============ POPUP: PILIH DOSEN ============ -->
    <div v-if="pickModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="closePickModal"></div>

      <div class="relative bg-white rounded-base shadow-xl w-full max-w-md p-6">
        <h3 class="text-base font-semibold text-heading mb-1">
          Tambah {{ pickModal.status === 'auditor' ? 'Auditor' : 'Auditee' }}
        </h3>
        <p class="text-sm text-body mb-5">Pilih dosen yang akan ditugaskan.</p>

        <Multiselect
          v-model="pickModal.dosenId"
          placeholder="Ketikan Nama Dosen"
          :searchable="true"
          :options="pickModal.status === 'auditor' ? availableForAuditor : availableForAuditee"
          valueProp="id"
          label="nama_gelar"
          track-by="nama_gelar"
          :limit="10"
          class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
        />

        <p v-if="errorPenugasan" class="text-sm text-red-500 mt-3">{{ errorPenugasan }}</p>

        <div class="flex items-center justify-end gap-3 mt-6">
          <button
            type="button"
            @click="closePickModal"
            class="text-sm font-medium text-body px-4 py-2 hover:bg-neutral-secondary-medium rounded-base transition-colors"
          >
            Batal
          </button>
          <button
            type="button"
            @click="handleAddPenugasan"
            :disabled="!pickModal.dosenId || submittingPenugasan"
            class="text-white bg-brand border border-transparent hover:bg-brand-strong shadow-xs font-medium rounded-base text-sm px-5 py-2.5 disabled:opacity-40"
          >
            {{ submittingPenugasan ? 'Menyimpan...' : 'Tambahkan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import ButtonComponent from '../../../../components/ButtonComponent.vue'
import Multiselect from '@vueform/multiselect'
import { reactive, ref, computed, onMounted, watch } from 'vue'
import '../../../../css/select.css'

import axiosClient from '@/axios'
import { notifyError } from '@/utils/notify'
import { confirmDialog } from '@/utils/confirmDialog'

const emit = defineEmits(['back', 'edit'])

const props = defineProps({
  tipe: { type: String, required: true },
  data: { type: Object, default: () => ({}) },
})

const model = reactive({
  id: null,
  tanggal_awal: null,
  tanggal_akhir: null,
  semester: null,
  nama_jadwal: null,
  area_audit: null,
})

const list_semester = computed(() => {
  const currentYear = new Date().getFullYear()
  const startYear = currentYear - 8
  const endYear = currentYear + 2
  const list = []
  for (let year = endYear; year >= startYear; year--) {
    list.push({ id: `${year}1`, name: `${year} / ${year + 1} Ganjil` })
    list.push({ id: `${year}2`, name: `${year} / ${year + 1} Genap` })
  }
  return list
})

// QOL fix (12 Sep 2026) - tombol Submit dulu tidak pernah di-disable/kasih indikator loading
// sama sekali, beda dengan tombol "Tambahkan" di panel Penugasan (submittingPenugasan) di file
// yang sama - jadi user bisa klik berkali-kali sementara request /jadwalaudit/data/store atau
// /data/update masih jalan (berpotensi bikin jadwal dobel kalau responsnya lambat).
const isSubmittingForm = ref(false)

const buttonSubmitForm = async () => {
  const requestData = {
    id: model.id,
    tanggal_awal: model.tanggal_awal,
    tanggal_akhir: model.tanggal_akhir,
    semester: model.semester,
    nama_jadwal: model.nama_jadwal,
    area_audit: model.area_audit,
  }

  isSubmittingForm.value = true
  try {
    if (props.tipe === 'create') {
      const response = await axiosClient.post('/jadwalaudit/data/store', requestData)
      // Langsung pindah ke mode Edit pakai data jadwal yang baru dibuat (butuh id-nya),
      // biar panel Penugasan Auditor/Auditee di bawah langsung kebuka - nggak perlu balik
      // ke daftar & klik Edit manual lagi. Event 'edit' ini sudah didengerin sama parent
      // (JadwalAudit.vue) buat set tipe_form = 'edit' + data_awal = data.
      emit('edit', response.data.data)
    } else {
      // Toast sukses ("Sukses") sudah otomatis dari interceptor axios.js - dulu ada alert()
      // manual duplikat di sini (dirapikan 10 Sep, lihat src/utils/notify.js).
      await axiosClient.post('/jadwalaudit/data/update', requestData)
      emit('back')
    }
  } catch (error) {
    console.error('Error saving data:', error)
    if (error.response && error.response.status === 400) {
      notifyError(error.response.data.error || 'Data validation error occurred.')
    } else {
      notifyError('An error occurred while saving data.')
    }
  } finally {
    isSubmittingForm.value = false
  }
}

const buttonBack = () => emit('back')
const buttonEdit = () => emit('edit')

// ============ PENUGASAN AUDITOR / AUDITEE (khusus mode edit) ============

const list_dosen = ref([]) // dari DosenController::getDosen() → { id, nama_gelar }
const auditorList = ref([]) // dari AuditorController::index()  → { id, nama_dosen, status, jadwal_spmi_id }
const auditeeList = ref([])
const loadingDosen = ref(false)
const loadingPenugasan = ref(false)
const submittingPenugasan = ref(false)
const errorPenugasan = ref('')

const availableForAuditor = computed(() =>
  list_dosen.value.filter((d) => !auditorList.value.some((a) => a.dosen_id === d.id)),
)
const availableForAuditee = computed(() =>
  list_dosen.value.filter((d) => !auditeeList.value.some((a) => a.dosen_id === d.id)),
)

// State popup pemilih dosen
const pickModal = reactive({
  open: false,
  status: null, // 'auditor' | 'auditee'
  dosenId: null,
})

function openPickModal(status) {
  pickModal.status = status
  pickModal.dosenId = null
  errorPenugasan.value = ''
  pickModal.open = true
}
function closePickModal() {
  pickModal.open = false
}

async function fetchDosen() {
  loadingDosen.value = true
  try {
    // TODO: sesuaikan path ini dengan route asli getDosen() di api.php
    const res = await axiosClient.get('/dosen/get-dosen')
    list_dosen.value = res.data
  } catch (error) {
    console.error('Gagal memuat daftar dosen:', error)
  } finally {
    loadingDosen.value = false
  }
}

async function fetchPenugasan() {
  if (!model.id) return
  // Dulu nggak ada indikator loading sama sekali di sini (dilaporkan user 11 Sep) - dari luar
  // kelihatan kayak nge-freeze pas nunggu 2 request auditor+auditee ini selesai. loadingPenugasan
  // dipakai template buat nampilin "Memuat..." di kedua kolom Auditor/Auditee selama fetch ini.
  loadingPenugasan.value = true
  try {
    const [resAuditor, resAuditee] = await Promise.all([
      axiosClient.post('/auditor/data', { params: { jadwal_id: model.id } }),
      axiosClient.post('/auditee/data', { params: { jadwal_id: model.id } }),
    ])
    auditorList.value = resAuditor.data.data ?? resAuditor.data
    auditeeList.value = resAuditee.data.data ?? resAuditee.data
  } catch (error) {
    console.error('Gagal memuat daftar penugasan:', error)
  } finally {
    loadingPenugasan.value = false
  }
}

async function handleAddPenugasan() {
  if (!pickModal.dosenId || !model.id) return

  errorPenugasan.value = ''
  submittingPenugasan.value = true
  const basePath = pickModal.status === 'auditor' ? '/auditor' : '/auditee'

  try {
    await axiosClient.post(`${basePath}/data/store`, {
      dosen_id: pickModal.dosenId,
      jadwal_spmi_id: model.id,
      status: pickModal.status,
    })
    await fetchPenugasan()
    closePickModal()
  } catch (error) {
    errorPenugasan.value = error.response?.data?.error || 'Terjadi kesalahan saat menyimpan.'
  } finally {
    submittingPenugasan.value = false
  }
}

async function handleSetKetua(item) {
  try {
    await axiosClient.post(`/auditor/data/set-ketua/${item.id}`)
    await fetchPenugasan()
  } catch (error) {
    // QOL fix (12 Sep 2026) - dulu cuma console.error, user tidak dapat feedback apa pun kalau
    // gagal (kelihatan seperti berhasil padahal tidak).
    console.error('Gagal menentukan Ketua Auditor:', error)
    notifyError(error.response?.data?.error || 'Gagal menentukan Ketua Auditor.')
  }
}

async function handleRemovePenugasan(item, status) {
  const ok = await confirmDialog(`Hapus penugasan ini dari daftar ${status}?`, {
    title: 'Hapus Penugasan',
    confirmText: 'Hapus',
    variant: 'danger',
  })
  if (!ok) return
  const basePath = status === 'auditor' ? '/auditor' : '/auditee'
  try {
    await axiosClient.post(`${basePath}/data/destroy/${item.id}`)
    await fetchPenugasan()
  } catch (error) {
    // QOL fix (12 Sep 2026) - sama seperti handleSetKetua di atas, dulu cuma console.error.
    console.error('Gagal menghapus penugasan:', error)
    notifyError(error.response?.data?.error || 'Gagal menghapus penugasan.')
  }
}

onMounted(() => {
  if (props.data) {
    Object.assign(model, props.data)
  }
  if (props.tipe === 'edit') {
    fetchDosen()
    fetchPenugasan()
  }
})

// Nangkep transisi create -> edit di komponen yang SAMA (setelah submit "Tambah Jadwal",
// parent ganti props.tipe jadi 'edit' tanpa remount komponen ini, jadi onMounted di atas
// nggak keulang). Begitu tipe berubah jadi 'edit', sinkronin model dari data baru + muat
// daftar dosen/penugasan biar panel Auditor/Auditee langsung keisi.
watch(
  () => props.tipe,
  (newTipe) => {
    if (newTipe === 'edit') {
      Object.assign(model, props.data)
      fetchDosen()
      fetchPenugasan()
    }
  },
)
</script>

<style scoped>
/* Animasi panel Penugasan Auditor/Auditee muncul (11 Sep 2026, permintaan user) - dipakai
   Transition name="panel-pop" yang bungkus panel ini di <template>. Sengaja plain CSS (bukan
   lang="scss") karena project ini nggak ada sass/sass-embedded terpasang - dulu <style> file ini
   kosong jadi nggak pernah ketrigger, begitu diisi konten beneran langsung 500 error karena
   preprocessor scss nggak ada. */
.panel-pop-enter-active {
  transition:
    opacity 0.35s ease,
    transform 0.35s ease;
}
.panel-pop-enter-from {
  opacity: 0;
  transform: translateY(12px);
}
.panel-pop-enter-to {
  opacity: 1;
  transform: translateY(0);
}
</style>
