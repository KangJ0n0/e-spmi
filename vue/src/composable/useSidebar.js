// src/composable/useSidebar.js
import { computed } from 'vue'
import { jwtDecode } from 'jwt-decode'

const menus = {
  admin: [
    { label: 'Dashboard', to: '/admin', icon: 'home' },
    { label: 'Jadwal Audit', to: '/admin/jadwal-audit', icon: 'calendar' },
    { label: 'Struktur Anggota', to: '/admin/struktur-anggota', icon: 'users' },
    { label: 'Auditor/Auditee', to: '/admin/auditor-auditee', icon: 'user' },
    { label: 'Kuisioner', to: '/admin/kuisioner', icon: 'note' },
    { label: 'Bank Pertanyaan', to: '/admin/bank-pertanyaan', icon: 'folder' },
    { label: 'Cetak Dokumen', to: '/admin/cetak-dokumen', icon: 'print' },
  ],
  auditor: [
    { label: 'Dashboard', to: '/auditor', icon: 'home' },
    // "Input Temuan" (Isi Instrumen) & "Pilih Pertanyaan" butuh id jadwal tertentu, jadi nggak
    // bisa jadi link sidebar yang statis - dibuka dari tombol di kartu jadwal di halaman ini.
    { label: 'Jadwal Audit', to: '/auditor/jadwal-auditor', icon: 'calendar' },
    { label: 'Cetak Dokumen', to: '/auditor/cetak-dokumen', icon: 'print' },
    // "Laporan Audit" & "Riwayat Audit" DIHAPUS DARI SINI - halamannya belum pernah dibuat sama
    // sekali (nggak ada file .vue atau route-nya). Ini bukan bug ke-klik-error, ini link ke
    // halaman yang emang belum ada. Kasih tau kalau memang mau dibangun sebagai fitur baru.
  ],
  auditee: [
    { label: 'Dashboard', to: '/auditee', icon: 'home' },
    { label: 'Jadwal Audit', to: '/auditee/jadwal-auditee', icon: 'calendar' },
    // "Unggah Dokumen"/"Hasil Temuan"/"Rencana Tindak Lanjut" DIHAPUS DARI SINI - sama kayak
    // "Laporan Audit"/"Riwayat Audit" di menu auditor, halamannya belum pernah dibuat sama
    // sekali (nggak ada file .vue atau route-nya di auditeeroute.js) - link ini SELALU 404 kalau
    // diklik. "Unggah Dokumen" (link Gdrive) sekarang jadi bagian dari form "Mulai Evaluasi
    // Diri" di halaman Jadwal Audit, bukan halaman terpisah. Kalau "Hasil Temuan"/"RTL" memang
    // mau dibangun jadi halaman sendiri, itu fitur baru yang perlu spek dulu.
  ],
}

const roleLabelMap = {
  admin: 'Admin LPMU',
  auditor: 'Auditor',
  auditee: 'Auditee',
}

export default function useSidebar() {
  const tokenPayload = computed(() => {
    const token = localStorage.getItem('token')
    if (!token) return null
    try {
      return jwtDecode(token)
    } catch {
      return null
    }
  })

  const roleName = computed(() => tokenPayload.value?.role_name ?? null)
  const roleLabel = computed(() => roleLabelMap[roleName.value] || 'Pengguna')
  const sidebarMenu = computed(() => menus[roleName.value] || [])
  // Prioritas: nama_dosen (Auditor/Auditee, dari tabel dosen) -> login_name (username akun,
  // dipakai Admin karena Admin nggak punya baris di tabel dosen) -> 'Pengguna' (fallback
  // terakhir kalau dua-duanya nggak ada di token, harusnya nggak pernah kejadian).
  const userName = computed(
    () => tokenPayload.value?.nama_dosen ?? tokenPayload.value?.login_name ?? 'Pengguna',
  )
  const userInitial = computed(() => userName.value?.charAt(0)?.toUpperCase() ?? '?')

  return { roleName, roleLabel, sidebarMenu, userName, userInitial }
}
