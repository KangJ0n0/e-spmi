// Fitur baru (15 Sep 2026) - user lapor teks Instrumen (Pernyataan Isi Standar, Butir
// Pertanyaan, Dokumen Dicek, jawaban Auditee, dst) yang aslinya list bernomor manual ("1) ...
// 2) ... 3) ...") ditulis dosen/tim penyusun di Excel/form, tapi di layar cuma tampil sebagai
// teks numpuk (whitespace-pre-line doang) - "jadi pusing yang baca". Fungsi ini deteksi baris
// yang formatnya "1) ...", "1. ...", atau "(1) ..." dan render jadi <ol> beneran, bukan cuma
// baris teks biasa. Dipakai LUAS (bukan cuma 1 halaman) - lihat CHANGES.md buat daftar lengkap
// halaman yang pakai ini.

// Bugfix (15 Sep 2026) - dulu SATU baris yang KEBETULAN diawali pola "angka + . / )" langsung
// dianggap list 1 item, mis. `butir_pertanyaan` yang isinya nomor urut soal ASLI dari Excel,
// "70. Uraikan pembahasan mengenai...". Dirender jadi `<ol><li>...</li></ol>` 1 item, dan browser/
// PDF OTOMATIS me-renumber isi <li> mulai dari "1." - nomor asli "70" HILANG, keganti angka "1"
// yang nggak ada artinya (dilaporkan user: "di butir pertanyaan tiba2 ada nomor 1"). Sekarang
// list CUMA dibentuk kalau ada MINIMAL 2 baris berurutan yang polanya cocok (list beneran) - kalau
// cuma 1 baris, dikembalikan APA ADANYA (teks asli, termasuk nomornya, TIDAK diotak-atik).

// Escape dulu SEBELUM diproses jadi HTML, biar teks asli yang kebetulan ada karakter <, >, & dari
// database TIDAK dianggap tag HTML beneran (mencegah tampilan berantakan/rusak, bukan soal
// keamanan pengguna luar - data ini cuma diinput Admin/Dosen sendiri).
function escapeHtml(text) {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

// Pola nomor yang dikenali: "1)", "1.", "(1)" di awal baris, diikuti spasi.
const POLA_NOMOR = /^\(?(\d{1,2})[.)]\s+(.*)$/

export function formatTeksBernomor(teks) {
  if (!teks) return ''
  const baris = String(teks).split('\n')

  const potongan = [] // { tipe: 'paragraf' | 'list', isi: string | string[] }
  let bufferList = [] // baris yang KEMUNGKINAN bagian list, nunggu dipastikan (>= 2 baris berurutan)

  const flushBuffer = () => {
    if (bufferList.length === 0) return
    if (bufferList.length === 1) {
      // Cuma 1 baris - BUKAN list beneran, kembalikan teks aslinya utuh (lihat bugfix di atas).
      potongan.push({ tipe: 'paragraf', isi: bufferList[0].asli })
    } else {
      potongan.push({ tipe: 'list', isi: bufferList.map((b) => b.isi) })
    }
    bufferList = []
  }

  for (const satuBaris of baris) {
    const cocok = satuBaris.match(POLA_NOMOR)
    if (cocok) {
      bufferList.push({ asli: satuBaris, isi: cocok[2] })
    } else {
      flushBuffer()
      potongan.push({ tipe: 'paragraf', isi: satuBaris })
    }
  }
  flushBuffer()

  return potongan
    .map((p) => {
      if (p.tipe === 'list') {
        const items = p.isi.map((item) => `<li>${escapeHtml(item)}</li>`).join('')
        return `<ol class="list-decimal pl-5 my-1 space-y-0.5">${items}</ol>`
      }
      // Baris kosong tetap dijaga jaraknya (dulu peran whitespace-pre-line), baris isi di-escape.
      return p.isi === '' ? '<br>' : `<div>${escapeHtml(p.isi)}</div>`
    })
    .join('')
}
