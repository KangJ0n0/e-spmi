import { onMounted, ref } from 'vue'
import axiosClient from '@/axios'

/**
 * Composable (12 Okt 2026) - ambil info jadwal (nama, area, tanggal) untuk label <JadwalLabel>.
 * Endpoint detail pertanyaan (/jadwal-audit/{id}/pertanyaan) tidak membawa data jadwal, jadi
 * diambil dari daftar jadwal milik user yang login (endpoint yang sama dengan halaman daftar
 * jadwalnya): '/auditor/jadwal-saya' atau '/auditee/jadwal-saya'.
 *
 * Gagal -> label tidak tampil, halaman tetap jalan seperti biasa.
 *
 * @param {string} endpoint - '/auditor/jadwal-saya' atau '/auditee/jadwal-saya'
 * @param {string|number} jadwalId - id jadwal (jadwal_spmi.id) yang sedang dibuka
 */
export function useJadwalInfo(endpoint, jadwalId) {
  const jadwalInfo = ref(null)

  const fetchJadwalInfo = async () => {
    try {
      const res = await axiosClient.get(endpoint)
      if (res?.isAxiosError || res?.response) return
      const daftar = res.data?.data || res.data || []
      const ketemu = daftar.find((x) => String(x.jadwal_spmi_id || x.id) === String(jadwalId))
      jadwalInfo.value = ketemu ? ketemu.jadwal || ketemu : null
    } catch {
      jadwalInfo.value = null
    }
  }

  onMounted(fetchJadwalInfo)

  return { jadwalInfo }
}
