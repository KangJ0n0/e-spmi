// src/composable/useSidebar.js
import { computed } from 'vue';
import { jwtDecode } from 'jwt-decode';

const menus = {
  admin: [
    { label: 'Dashboard', to: '/admin', icon: 'home' },
    { label: 'Jadwal Audit', to: '/admin/jadwal-audit', icon: 'calendar' },
    { label: 'Struktur Anggota', to: '/admin/struktur-anggota', icon: 'users' },
    { label: 'Auditor', to: '/admin/users', icon: 'user' },
    { label: 'Auditee', to: '/admin/auditee', icon: 'user' },
  ],
  auditor: [
    { label: 'Dashboard', to: '/auditor', icon: 'home' },
    { label: 'Jadwal Audit', to: '/auditor/jadwal-audit', icon: 'calendar' },
    { label: 'Input Temuan', to: '/auditor/temuan', icon: 'note' },
    { label: 'Laporan Audit', to: '/auditor/laporan', icon: 'file' },
    { label: 'Riwayat Audit', to: '/auditor/riwayat', icon: 'clock' },
  ],
  auditee: [
    { label: 'Dashboard', to: '/auditee', icon: 'home' },
    { label: 'Jadwal Audit', to: '/auditee/jadwal-audit', icon: 'calendar' },
    { label: 'Unggah Dokumen', to: '/auditee/dokumen', icon: 'upload' },
    { label: 'Hasil Temuan', to: '/auditee/temuan', icon: 'search' },
    { label: 'Rencana Tindak Lanjut', to: '/auditee/rtl', icon: 'check' },
  ],
};

const roleLabelMap = {
  admin: 'Admin LPMU',
  auditor: 'Auditor',
  auditee: 'Auditee',
};

export default function useSidebar() {
  const tokenPayload = computed(() => {
    const token = localStorage.getItem('token');
    if (!token) return null;
    try {
      return jwtDecode(token);
    } catch {
      return null;
    }
  });

  const roleName = computed(() => tokenPayload.value?.role_name ?? null);
  const roleLabel = computed(() => roleLabelMap[roleName.value] || 'Pengguna');
  const sidebarMenu = computed(() => menus[roleName.value] || []);
  const userName = computed(() => tokenPayload.value?.name ?? tokenPayload.value?.email ?? 'Pengguna');
  const userInitial = computed(() => userName.value?.charAt(0)?.toUpperCase() ?? '?');

  return { roleName, roleLabel, sidebarMenu, userName, userInitial };
}