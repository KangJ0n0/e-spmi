import axiosClient from '@/axios'
import { ref } from 'vue'
export default function useStrukturAnggota() {
  const data_struktur_anggota = ref([])
  
    const getStrukturAnggota = async (page = null, data, limit = null, dropdown = false) => {
      const url = `/struktur_anggota/data${page ? `?page=${page}` : limit ? `?limit=${limit}` : ''}`
      let response = await axiosClient.post(url, data)
  
      if (dropdown) {
        return response
      } else {
        data_struktur_anggota.value = response.data
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
    getStrukturAnggota,
    storeStrukturAnggota,
    updateStrukturAnggota,
    destroyStrukturAnggota
  }
}