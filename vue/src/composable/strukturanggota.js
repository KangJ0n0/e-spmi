import axiosClient from '@/axios'
import { ref } from 'vue'
export default function useStrukturAnggota() {
  const data_struktur_anggota = ref([])
  // Mulai true (bukan false) supaya tabel langsung nampilin "Memuat data..." begitu halaman
  // dibuka, bukan "Data Not Found" dulu selama debounce 1 detik sebelum request pertama jalan.
  const loading = ref(true)

    const getStrukturAnggota = async (page = null, data, limit = null, dropdown = false) => {
      loading.value = true
      try {
        const url = `/struktur_anggota/data${page ? `?page=${page}` : limit ? `?limit=${limit}` : ''}`
        let response = await axiosClient.post(url, data)

        if (dropdown) {
          return response
        } else {
          data_struktur_anggota.value = response.data
        }
      } finally {
        loading.value = false
      }
    }
  const storeStrukturAnggota = async (data) => {
    const response = await axiosClient.post('/struktur_anggota/data/store', data)
    

    return { error: response.response }
  }
  
  const updateStrukturAnggota = async (data) => {
    const response = await axiosClient.post('/struktur_anggota/data/update', data)
    return { error: response.response }
  }

  const destroyStrukturAnggota = async (data) => {
    const response = await axiosClient.post('/struktur_anggota/data/destroy', data)
    return { error: response.response }
  }

  return {
    data_struktur_anggota,
    loading,
    getStrukturAnggota,
    storeStrukturAnggota,
    updateStrukturAnggota,
    destroyStrukturAnggota
  }
}