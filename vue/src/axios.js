import axios from 'axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { useStore } from '@/stores'
import router from '@/router'

const axiosClient = axios.create({
  baseURL: "http://127.0.0.1:8000/api", // Replace with your API base URL

})

let isLoggingOut = false

// ===== Loading indicator (progress bar di atas, lihat TopLoadingBar.vue) =====
// Dihitung pakai counter (bukan boolean langsung) karena beberapa halaman menembak beberapa
// request axios bersamaan (mis. Promise.all) - kalau langsung set boolean, request pertama yang
// selesai akan mematikan loading walau request lain masih jalan. `setLoader` di Pinia store
// sudah ada dari awal tapi sebelumnya tidak pernah dipanggil dari mana pun.
let pendingRequests = 0
const updateLoader = () => {
  useStore().setLoader(pendingRequests > 0)
}
const startLoading = () => {
  pendingRequests += 1
  updateLoader()
}
const stopLoading = () => {
  pendingRequests = Math.max(0, pendingRequests - 1)
  updateLoader()
}

// ===== Request Interceptor =====
axiosClient.interceptors.request.use(
  (config) => {
    startLoading()
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => {
    stopLoading()
    return Promise.reject(error)
  },
)

// ===== Response Interceptor =====
axiosClient.interceptors.response.use(
  // Success handler
  (response) => {
    stopLoading()
    if (response?.data?.message) {
      toast(response.data.message, {
        theme: 'auto',
        type: 'success',
        autoClose: 3000,
        dangerouslyHTMLString: true,
      })
    }
    return response
  },

  // Error handler
  async (error) => {
    stopLoading()
    // If no server response at all (network down, timeout)
    if (!error.response) {
      console.error('Network error:', error)
      toast('Network error. Please try again.', { theme: 'auto', type: 'error' })
      // Let the calling code decide
      return Promise.reject(error)
    }

    const { status, data, request } = error.response
    // Backend kamu balikin pesan pakai key `error` (mis. JawabanController: response()->json(['error' => '...'], 403)),
    // bukan `message`. Sebelumnya di sini cuma dicek data?.message, jadi kalau backend cuma kirim `error`,
    // toast selalu jatuh ke fallback generik "An error occurred" dan pesan aslinya dari backend ke-buang.
    let message = data?.message || data?.error || 'An error occurred'

    // Special: parse Blob with JSON error
    if (
      request?.responseType === 'blob' &&
      data instanceof Blob &&
      data.type?.toLowerCase().includes('json')
    ) {
      try {
        const text = await data.text()
        const json = JSON.parse(text)
        message = json?.message || message
      } catch (e) {
        console.error('Error parsing blob JSON', e)
      }
    }

    // Handle status codes
    switch (status) {
      case 400:
      case 404:
        case 422:
      case 500:

        toast(message, { theme: 'auto', type: 'error', autoClose: 3000 })
        return error // handled internally, no need to .catch()

      case 401:
        // 401 = beneran nggak terautentikasi (token invalid/expired/nggak ada). INI yang cocok
        // ditampilkan sebagai "Session expired" + hapus token, karena token-nya emang udah nggak
        // valid buat request apa pun ke API.
        if (!isLoggingOut) {
          isLoggingOut = true
          toast('Session expired. Please login again.', {
            theme: 'auto',
            type: 'error',
            autoClose: 3000,
            onClose: () => {
              localStorage.removeItem('token')
              // QOL fix (12 Sep 2026) - dulu token dihapus di sini TAPI tidak pernah diarahkan
              // balik ke halaman Login. Efeknya: user tetap di halaman yang sama, kelihatan
              // normal padahal sesinya sudah dianggap habis, sampai dia klik sesuatu lagi yang
              // baru men-trigger guard router (beforeEach) buat nge-redirect. Sekarang diarahkan
              // langsung begitu toast-nya selesai (baik ditutup manual atau autoClose 3 detik).
              if (router.currentRoute.value.name !== 'Login') {
                router.push('/login')
              }
              isLoggingOut = false
            },
          })
        }
        return error // handled internally

      case 403:
        // 403 = token-nya VALID, tapi aksi ini ditolak karena alasan bisnis (mis. di luar jendela
        // tanggal jadwal audit, bukan pemilik resource, dsb - lihat cekJadwalDanTanggal() di
        // JawabanController). Sebelumnya 403 disamain dengan 401 ("Session expired" + hapus
        // token), jadi error bisnis yang sah (mis. "Waktu pengisian audit sudah lewat...") malah
        // nampilin pesan salah dan malah maksa user logout padahal token-nya masih OK.
        toast(message, { theme: 'auto', type: 'error', autoClose: 3000 })
        return error // handled internally

      default:
        toast(message, { theme: 'auto', type: 'error', autoClose: 3000 })
        // Default: let caller handle it too if needed
        return Promise.reject(error)
    }
  },
)

export default axiosClient