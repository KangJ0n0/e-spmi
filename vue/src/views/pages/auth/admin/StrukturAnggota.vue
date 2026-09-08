<template>
  <div v-if="!show_form" class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-500 whitespace-nowrap">Status</label>
        <select
          v-model="filter.status"
          @change="filterStatus"
          class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#0F2A4A]"
        >
          <option :value="null">Semua</option>
          <option value="KETUA">Ketua</option>
          <option value="KOORDINATOR">Koordinator</option>
          <option value="STAFF">Staff</option>
        </select>
      </div>
      <ButtonComponent variant="primary" @click="buttonTambah">
        Tambah Anggota
      </ButtonComponent>
    </div>
    <TableComponent
          :headers="headers"
          @per_page="handlePerPageChange"
          :dataTable="data_table"
          :loading="loading"
          :show_search="true"
          @search="filterSearch"
          @pagechanged="onPageChange"
          @delete="buttonDelete"
          @detail="buttonDetail"
          @edit="buttonEdit"
        ></TableComponent>
  </div>


        <StrukturAnggotaForm
    v-if="show_form"
    @back="buttonKembali"
    :data="data_awal"
    :tipe="tipe_form"
    @edit="buttonEdit"
  />
</template>

<script setup>
import useStrukturAnggota from './../../../../composable/strukturanggota'
import { debounce } from 'lodash'
import TableComponent from '@/components/TableComponent.vue'
import ButtonComponent from '@/components/ButtonComponent.vue'
import StrukturAnggotaForm from './StrukturAnggotaForm.vue'
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'
const filter = reactive({
  status:null,
  filter: null,
  paginate: null
})
const data_table = reactive({
  data: [],
  page: 1,
  per_page: 0,
  total: 0,
  last_page: 0,
  from: 0,
  to: 0
})
const headers = computed(() => [
  { key: 'nama', label: 'Nama', view: 'title' },
  { key: 'status', label: 'Status' },
  { key: 'jabatan', label: 'Jabatan' },
  { button: ['Delete', 'Detail', 'Edit'] }
])
const { data_struktur_anggota, loading, getStrukturAnggota, storeStrukturAnggota, updateStrukturAnggota, destroyStrukturAnggota} = useStrukturAnggota()

const debounceStrukturAnggota = debounce(async (data_table, filter) => {
 
  await getStrukturAnggota(data_table.page, { ...filter })

 
}, 1000)

watch(data_struktur_anggota, (data) => {
  if (data) {
    Object.assign(data_table, data)
  }
})

const show_form = ref(false)
const tipe_form = ref('create')

const buttonTambah = async () => {
  
  tipe_form.value = 'create'
  show_form.value = true
  
}
const data_awal = ref({})
const buttonKembali = async () => {

  show_form.value = false
  filter.paginate = 25
  filter.status= null
  data_awal.value = {}
  data_table.page = 1
  await getStrukturAnggota(data_table.page, { ...filter })
  
}
const buttonEdit = async (id) => {
  
  show_form.value = true
  tipe_form.value = 'edit'
  data_awal.value = id
  
}
const buttonDetail = async (id) => {
  
  show_form.value = true
  tipe_form.value = 'detail'
  data_awal.value = id
  
}

const buttonDelete = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
    await destroyStrukturAnggota(id)
    data_table.page = 1
    await getStrukturAnggota(data_table.page, { ...filter })
  }
}
// TableComponent ngirim query pencarian langsung sebagai string lewat event @search (bukan
// event object kayak keyboard event) - dulu function ini nunggu event.altKey yang nggak pernah
// ada, jadi nggak pernah kepanggil beneran walau sempat ke-emit.
const filterSearch = debounce(async (query) => {
  filter.filter = query
  data_table.page = 1
  await getStrukturAnggota(data_table.page, { ...filter })
}, 500)

const filterStatus = async () => {
  data_table.page = 1
  await getStrukturAnggota(data_table.page, { ...filter })
}
const handlePerPageChange = async (id) => {
 
  filter.paginate = id
  data_table.page = 1
  await getStrukturAnggota(data_table.page, { ...filter })
 
}
const onPageChange = async (page) => {
  data_table.page = page
  await getStrukturAnggota(data_table.page, { ...filter })
}

onMounted(async () => {

  filter.paginate = 25
await debounceStrukturAnggota(data_table.page, { ...filter })
})
</script>

