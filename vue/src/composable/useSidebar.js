// src/composable/useSidebar.js
import { computed } from 'vue'
import { jwtDecode } from 'jwt-decode'

const menus = {
  admin: [
    // exact: true (11 Sep 2026, lihat isMenuActive di bawah) - tanpa ini Dashboard bakal
    // ke-highlight terus di SEMUA halaman Admin lain (semuanya diawali '/admin').
    { label: 'Dashboard', to: '/admin', icon: 'home', exact: true },
    { label: 'Jadwal Audit', to: '/admin/jadwal-audit', icon: 'calendar' },
    { label: 'Struktur Anggota', to: '/admin/struktur-anggota', icon: 'users' },
    { label: 'Auditor/Auditee', to: '/admin/auditor-auditee', icon: 'user' },
    { label: 'Kuisioner', to: '/admin/kuisioner', icon: 'note' },
    { label: 'Instrumen', to: '/admin/bank-pertanyaan', icon: 'folder' },
    { label: 'Cetak Dokumen', to: '/admin/cetak-dokumen', icon: 'print' },
  ],
  auditor: [
    // exact: true - route root '/auditor' HARUS dicek sama-persis, bukan startsWith, soalnya
    // semua route auditor lain juga diawali '/auditor' (lihat isMenuActive di bawah).
    { label: 'Dashboard', to: '/auditor', icon: 'home', exact: true },
    // "Jadwal Audit" gabungan (11 Sep 2026) DIPECAH jadi 2 menu terpisah sesuai permintaan user
    // ("terlalu banyak merangkum hal jadi satu") - awalnya dikasih nama "Pilih Pertanyaan" &
    // "Nilai Instrumen", TAPI langsung direvisi user di pesan berikutnya jadi "Pilih Instrumen" &
    // "Jawaban & Penilaian" (nama akhir yang dipakai). Path/route/nama file TIDAK ikut diubah,
    // cuma teks yang tampil ke user (label sidebar, judul halaman) - lihat auditorroute.js.
    // Masing-masing nuju halaman daftar-jadwal barunya sendiri (PilihPertanyaanList.vue /
    // NilaiInstrumenList.vue), lalu dari situ ke halaman detail per-jadwal yang butuh :id.
    // `match` = daftar prefix path yang dianggap "lagi di menu ini" - termasuk halaman detail
    // per-jadwal (/auditor/pilih-pertanyaan/:id dst) biar sidebar tetap ke-highlight pas Anda
    // masuk ke situ (sebelumnya TIDAK ke-detect karena beda route record dari link sidebar-nya).
    {
      label: 'Pilih Instrumen',
      to: '/auditor/pilih-pertanyaan',
      icon: 'calendar',
      match: ['/auditor/pilih-pertanyaan'],
    },
    {
      label: 'Jawaban & Penilaian',
      to: '/auditor/nilai-instrumen',
      icon: 'check',
      match: ['/auditor/nilai-instrumen'],
    },
    { label: 'Cetak Dokumen', to: '/auditor/cetak-dokumen', icon: 'print' },
    // "Laporan Audit" & "Riwayat Audit" DIHAPUS DARI SINI - halamannya belum pernah dibuat sama
    // sekali (nggak ada file .vue atau route-nya). Ini bukan bug ke-klik-error, ini link ke
    // halaman yang emang belum ada. Kasih tau kalau memang mau dibangun sebagai fitur baru.
  ],
  auditee: [
    { label: 'Dashboard', to: '/auditee', icon: 'home', exact: true },
    // "Jadwal Audit" gabungan DIPECAH (11 Sep 2026), sama alasan/pola kayak menu Auditor di
    // atas. "Lihat Hasil" sengaja nuju /auditee/hasil-evaluasi (halaman daftar BARU), TAPI
    // match-nya mencakup /auditee/lihat-hasil juga (halaman detail hasil per-jadwal yang sudah
    // ada sejak 10 Sep) biar 1 menu yang sama ke-highlight di kedua halaman itu.
    {
      label: 'Evaluasi Diri',
      to: '/auditee/evaluasi-diri',
      icon: 'calendar',
      match: ['/auditee/evaluasi-diri', '/auditee/instrumen-auditee'],
    },
    {
      label: 'Lihat Hasil',
      to: '/auditee/hasil-evaluasi',
      icon: 'check',
      match: ['/auditee/hasil-evaluasi', '/auditee/lihat-hasil'],
    },
    // "Unggah Dokumen"/"Hasil Temuan"/"Rencana Tindak Lanjut" DIHAPUS DARI SINI - sama kayak
    // "Laporan Audit"/"Riwayat Audit" di menu auditor, halamannya belum pernah dibuat sama
    // sekali (nggak ada file .vue atau route-nya di auditeeroute.js) - link ini SELALU 404 kalau
    // diklik. "Unggah Dokumen" (link Gdrive) sekarang jadi bagian dari form "Mulai Evaluasi
    // Diri" di halaman Evaluasi Diri, bukan halaman terpisah. Kalau "Hasil Temuan"/"RTL" memang
    // mau dibangun jadi halaman sendiri, itu fitur baru yang perlu spek dulu.
  ],
}

// Menentukan apakah 1 item menu sidebar harus ke-highlight aktif untuk path route saat ini
// (11 Sep 2026). SEBELUMNYA pakai <router-link active-class="..."> bawaan Vue Router, yang
// nge-cek kesamaan ROUTE RECORD (bukan string path) - jadi begitu route detail (mis.
// /auditor/nilai-instrumen/:id atau /auditee/lihat-hasil?id=...) route record-nya BEDA dari
// link sidebar-nya, active-class nggak pernah nyala sama sekali, sidebar kelihatan "kosong"/
// nggak ke-detect lagi posisinya (dilaporkan user). Sekarang dicek manual pakai string prefix
// path (boundary-aware: '/auditor/nilai-instrumen' match '/auditor/nilai-instrumen/abc' tapi
// TIDAK match '/auditor/nilai-instrumen-lain'), pakai daftar `match` per-item kalau ada (buat
// menu yang detail-nya beda path, kayak Lihat Hasil), atau `to` item itu sendiri kalau tidak.
// `exact: true` dipakai khusus Dashboard biar nggak ke-highlight di semua halaman lain (semua
// route role tsb kan sama-sama diawali '/admin', '/auditor', '/auditee').
export function isMenuActive(currentPath, item) {
  if (item.exact) return currentPath === item.to
  const prefixes = item.match || [item.to]
  return prefixes.some(
    (prefix) => currentPath === prefix || currentPath.startsWith(prefix + '/'),
  )
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
