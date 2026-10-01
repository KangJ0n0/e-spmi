<!--
  Komponen baru (30 Sep 2026) - fitur "simpan per kolom" di NilaiInstrumenAuditor.vue. Dulu
  semua kolom penilaian (Faktor Pendukung, Kategori Temuan, Rekomendasi, dst) cuma textarea/
  select polos yang ke-submit BARENG di tombol "Simpan Penilaian" paling akhir (1x submit-semua).
  Sekarang tiap kolom PUNYA tombol Simpan/Edit sendiri-sendiri, biar >1 Auditor di jadwal yang
  sama bisa saling melengkapi/merevisi kolom yang beda-beda kapan saja (kolaboratif) - progress
  kelihatan dari kolom mana yang sudah ada badge "Tersimpan" vs "Belum diisi".

  Dipakai berulang (7x) di NilaiInstrumenAuditor.vue buat tiap kolom penilaian, jadi ditarik jadi
  komponen sendiri biar nggak duplikasi markup Simpan/Edit/textarea/select 7 kali.
-->
<template>
  <div>
    <div class="flex items-center justify-between mb-1">
      <label class="block text-sm font-semibold text-gray-700">
        {{ label }} <span v-if="required" class="text-red-500">*</span>
      </label>
      <span
        v-if="modelValue && !editing"
        class="text-[11px] text-green-600 font-medium whitespace-nowrap"
      >
        &#10003; Tersimpan
      </span>
    </div>

    <template v-if="editing">
      <textarea
        v-if="type === 'textarea'"
        v-model="localValue"
        :rows="rows"
        class="w-full border border-gray-300 rounded-md p-3 text-sm"
      ></textarea>
      <select
        v-else-if="type === 'select'"
        v-model="localValue"
        class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
      >
        <option value="" disabled>Pilih...</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
      <input
        v-else
        :type="type"
        v-model="localValue"
        :placeholder="placeholder"
        class="w-full border border-gray-300 rounded-md p-3 text-sm"
      />

      <div class="flex justify-end gap-2 mt-2">
        <button
          type="button"
          @click="batal"
          class="px-3 py-1 text-xs border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Batal
        </button>
        <button
          type="button"
          @click="simpan"
          :disabled="saving || !localValue"
          class="px-3 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>
    </template>

    <template v-else>
      <div
        v-if="modelValue"
        class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-md p-3 whitespace-pre-line"
      >
        {{ modelValue }}
      </div>
      <div
        v-else
        class="text-xs text-gray-400 italic bg-gray-50 border border-dashed border-gray-300 rounded-md p-3"
      >
        Belum diisi.
      </div>
      <button
        type="button"
        @click="mulaiEdit"
        class="mt-2 text-xs font-semibold text-blue-600 hover:text-blue-800"
      >
        {{ modelValue ? 'Edit' : 'Isi' }}
      </button>
    </template>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: [String, null], default: '' },
  label: { type: String, required: true },
  type: { type: String, default: 'textarea' }, // 'textarea' | 'select' | 'date' | 'text'
  rows: { type: Number, default: 3 },
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: true },
  saving: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'simpan'])

// Belum pernah diisi sama sekali -> langsung buka mode edit (nggak perlu klik "Isi" dulu),
// biar UX-nya nggak beda jauh dari textarea polos yang dulu buat kolom yang masih kosong.
const editing = ref(!props.modelValue)
const localValue = ref(props.modelValue || '')

// Kalau modelValue berubah dari luar (mis. hasil simpanan Auditor lain kepanggil ulang lewat
// fetch), sinkronkan localValue - tapi CUMA kalau lagi nggak mode edit (jangan timpa isian yang
// lagi diketik user).
watch(
  () => props.modelValue,
  (v) => {
    if (!editing.value) localValue.value = v || ''
  },
)

const mulaiEdit = () => {
  localValue.value = props.modelValue || ''
  editing.value = true
}

const batal = () => {
  localValue.value = props.modelValue || ''
  editing.value = props.modelValue ? false : true
}

// Optimis: langsung keluar dari mode edit begitu diklik Simpan (nggak nunggu response server
// dulu) - kalau BENERAN gagal (network error dll), axiosClient interceptor sudah otomatis nampilin
// toast error (lihat pola gagal() di seluruh file Vue proyek ini), dan isian localValue tetap
// kepegang di modelValue (parent) jadi nggak hilang, tinggal klik Edit lagi buat coba simpan ulang.
const simpan = () => {
  emit('update:modelValue', localValue.value)
  emit('simpan', localValue.value)
  editing.value = false
}

// Fitur baru (1 Okt 2026) - "Simpan Semua yang Sudah Diisi" di NilaiInstrumenAuditor.vue: parent
// butuh cara buat (1) ngecek kolom ini lagi diedit DAN sudah ada isian yang belum diklik Simpan
// sendiri, dan (2) nandain kolom ini "tersimpan" (keluar dari mode edit) setelah bulk-save-nya
// sukses - TANPA harus nunggu user klik tombol Simpan di tiap kolom satu-satu.
defineExpose({
  ambilNilaiBelumTersimpan: () => (editing.value && localValue.value ? localValue.value : null),
  tandaiTersimpan: () => {
    editing.value = false
  },
})
</script>
