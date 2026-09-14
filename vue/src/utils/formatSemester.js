// Format kode semester 5 digit ("20272") jadi label yang manusiawi ("Genap 2027/2028") -
// dipakai di banyak halaman (Admin/Auditor/Auditee) yang sebelumnya nampilin kode mentahnya
// langsung ke user (dilaporkan user 13 Sep 2026: "bagian fe semester bisa dibuat ganjil genap
// dan periodenya daripada seperti 20272?").
//
// Konvensi kode: 4 digit tahun ajaran mulai + 1 digit tipe (1 = Ganjil, 2 = Genap), mis. "20272"
// = Genap 2027/2028. Konvensi yang SAMA dipakai di backend (DokumenAuditController::
// formatPeriodeAudit(), JadwalAuditExport::formatSemester() - sebelum dihapus bareng fitur
// Export) dan sudah ada di frontend (KonfigurasiNomorDokumen.vue::formatLabelSemester()) -
// fungsi ini menyatukan logika yang sama biar tidak perlu ditulis ulang di tiap halaman yang
// menampilkan semester.
//
// Kalau kode-nya tidak dikenali (bukan 5 digit angka, atau tipe bukan 1/2), dikembalikan apa
// adanya biar tidak menyembunyikan data yang mungkin memang belum diisi/formatnya beda - bukan
// dipaksa jadi string kosong.
export function formatSemester(semester) {
  if (!semester) return ''
  const kode = String(semester)
  if (kode.length !== 5 || !/^\d+$/.test(kode)) {
    return kode
  }

  const tahun = parseInt(kode.slice(0, 4), 10)
  const tipe = kode.slice(4, 5)

  if (tipe === '1') return `Ganjil ${tahun}/${tahun + 1}`
  if (tipe === '2') return `Genap ${tahun}/${tahun + 1}`
  return kode
}
