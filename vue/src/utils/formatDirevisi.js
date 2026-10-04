// Format waktu "Direvisi pada" (4 Okt 2026) - dipakai bareng di halaman Auditor & Auditee.
// Input: string datetime dari API (kolom jawabans.direvisi_pada, ISO). Kosong/invalid -> ''.
export const formatDirevisiPada = (nilai) => {
  if (!nilai) return ''
  const d = new Date(nilai)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
