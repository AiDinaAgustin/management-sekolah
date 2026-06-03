<template>
  <div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
      <div class="overflow-hidden rounded-[2rem] bg-gradient-to-r from-indigo-600 via-indigo-500 to-blue-500 p-8 text-white shadow-[0_24px_50px_rgba(79,70,229,0.28)]">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div class="max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-indigo-100/80">Welcome Back</p>
            <h1 class="mt-3 text-3xl font-bold leading-tight">{{ heroTitle }}</h1>
            <p class="mt-3 text-sm text-indigo-50/85">
              {{ heroDescription }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
              <NuxtLink :to="primaryShortcut?.to || '/absensi'" class="rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-md shadow-indigo-900/10">{{ primaryShortcut?.label || 'Input Absensi' }}</NuxtLink>
              <NuxtLink :to="secondaryShortcut?.to || '/nilai'" class="rounded-2xl border border-white/30 px-5 py-3 text-sm font-semibold text-white/95">{{ secondaryShortcut?.label || 'Input Nilai' }}</NuxtLink>
            </div>
          </div>
          <div class="grid w-full max-w-sm grid-cols-2 gap-3">
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-sm">
              <p class="text-xs text-indigo-100/80">{{ heroCards[0]?.label }}</p>
              <p class="mt-2 text-2xl font-bold">{{ heroCards[0]?.value }}</p>
            </div>
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-sm">
              <p class="text-xs text-indigo-100/80">{{ heroCards[1]?.label }}</p>
              <p class="mt-2 text-2xl font-bold">{{ heroCards[1]?.value }}</p>
            </div>
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-sm">
              <p class="text-xs text-indigo-100/80">{{ heroCards[2]?.label }}</p>
              <p class="mt-2 text-2xl font-bold">{{ heroCards[2]?.value }}</p>
            </div>
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-sm">
              <p class="text-xs text-indigo-100/80">{{ heroCards[3]?.label }}</p>
              <p class="mt-2 text-2xl font-bold">{{ heroCards[3]?.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.08)] backdrop-blur">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-semibold text-slate-900">Agenda Hari Ini</p>
            <p class="text-xs text-slate-400">Jadwal utama yang perlu dipantau</p>
          </div>
          <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">Today</span>
        </div>
        <div class="mt-5 space-y-4">
          <div v-for="(item, index) in dashboard.agenda" :key="`${item.title}-${index}`" class="rounded-2xl p-4" :class="index === 0 ? 'bg-gradient-to-r from-indigo-500 to-blue-500 text-white shadow-md shadow-indigo-200' : 'border border-slate-100 bg-slate-50/80'">
            <p class="text-sm font-semibold" :class="index === 0 ? 'text-white' : 'text-slate-800'">{{ item.title }}</p>
            <p class="mt-1 text-xs" :class="index === 0 ? 'text-indigo-100' : 'text-slate-400'">{{ item.subtitle }} - {{ item.meta }}</p>
          </div>
          <div v-if="!dashboard.agenda.length" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-6 text-center text-sm text-slate-400">
            Belum ada agenda untuk ditampilkan.
          </div>
        </div>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatCard v-for="(card, index) in statCards" :key="`${card.title}-${index}`" :title="card.title" :value="card.value" :description="card.description" />
    </div>

    <div>
      <div class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.08)] backdrop-blur">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-900">Aktivitas Hari Ini</h3>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Live update</span>
        </div>
        <div class="mt-5 space-y-4 text-sm text-slate-600">
          <div v-for="(activity, index) in dashboard.activities" :key="`${activity.title}-${index}`" class="flex items-center gap-4 rounded-2xl bg-slate-50/90 p-4">
            <div class="h-3 w-3 rounded-full" :class="activity.tone === 'success' ? 'bg-emerald-400' : activity.tone === 'warning' ? 'bg-amber-400' : 'bg-indigo-500'"></div>
            <div>
              <p class="font-medium text-slate-800">{{ activity.title }}</p>
              <p class="text-xs text-slate-400">{{ activity.description }}<span v-if="activity.time"> · {{ activity.time }}</span></p>
            </div>
          </div>
          <div v-if="!dashboard.activities.length" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/90 p-6 text-center text-sm text-slate-400">
            Belum ada aktivitas terbaru.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' });

type DashboardAgendaItem = {
  title: string;
  subtitle: string;
  meta: string;
  tone: string;
};

type DashboardActivityItem = {
  title: string;
  description: string;
  time?: string;
  tone: string;
};

type DashboardResponse = {
  role: 'admin' | 'guru';
  summary: Record<string, string | number>;
  agenda: DashboardAgendaItem[];
  activities: DashboardActivityItem[];
};

const auth = useAuthStore();
const dashboard = ref<DashboardResponse>({
  role: auth.user?.role === 'guru' ? 'guru' : 'admin',
  summary: {},
  agenda: [],
  activities: [],
});

const heroTitle = computed(() => auth.user?.role === 'guru' ? 'Halo, Guru' : 'Halo, Admin Sekolah');
const heroDescription = computed(() => auth.user?.role === 'guru'
  ? 'Pantau jadwal mengajar, absensi, dan progres input nilai dari satu dashboard yang lebih jelas.'
  : 'Kelola aktivitas harian, kehadiran, dan penilaian siswa lebih cepat dari satu dashboard yang lebih nyaman dilihat.');

const heroCards = computed(() => auth.user?.role === 'guru'
  ? [
      { label: 'Jadwal Hari Ini', value: dashboard.value.summary.jadwal_hari_ini ?? 0 },
      { label: 'Kelas Diampu', value: dashboard.value.summary.kelas_diampu ?? 0 },
      { label: 'Input Nilai', value: dashboard.value.summary.input_nilai_selesai ?? 0 },
      { label: 'Absensi Hari Ini', value: dashboard.value.summary.absensi_hari_ini ?? 0 },
    ]
  : [
      { label: 'Kehadiran Hari Ini', value: `${dashboard.value.summary.kehadiran_hari_ini ?? 0}%` },
      { label: 'Guru Aktif', value: dashboard.value.summary.total_guru ?? 0 },
      { label: 'Rombel', value: dashboard.value.summary.total_kelas ?? 0 },
      { label: 'Nilai Bulan Ini', value: dashboard.value.summary.input_nilai_bulan_ini ?? 0 },
    ]);

const statCards = computed(() => auth.user?.role === 'guru'
  ? [
      { title: 'Jadwal Hari Ini', value: dashboard.value.summary.jadwal_hari_ini ?? 0, description: 'Sesi mengajar hari ini' },
      { title: 'Kelas Diampu', value: dashboard.value.summary.kelas_diampu ?? 0, description: 'Kelas pada jadwal aktif' },
      { title: 'Input Nilai', value: dashboard.value.summary.input_nilai_selesai ?? 0, description: 'Update nilai hari ini' },
      { title: 'Absensi Hari Ini', value: dashboard.value.summary.absensi_hari_ini ?? 0, description: 'Data absensi yang diinput' },
    ]
  : [
      { title: 'Total Siswa', value: dashboard.value.summary.total_siswa ?? 0, description: 'Data aktif tahun ini' },
      { title: 'Total Guru', value: dashboard.value.summary.total_guru ?? 0, description: 'Guru aktif' },
      { title: 'Total Kelas', value: dashboard.value.summary.total_kelas ?? 0, description: 'Semua rombel' },
      { title: 'Kehadiran Hari Ini', value: `${dashboard.value.summary.kehadiran_hari_ini ?? 0}%`, description: 'Rekap semua kelas' },
    ]);

const primaryShortcut = computed(() => auth.user?.role === 'guru'
  ? { label: 'Input Absensi', to: '/absensi' }
  : { label: 'Kelola Siswa', to: '/siswa' });
const secondaryShortcut = computed(() => auth.user?.role === 'guru'
  ? { label: 'Input Nilai', to: '/nilai' }
  : { label: 'Cek Absensi', to: '/absensi' });

const fetchDashboard = async () => {
  const endpoint = auth.user?.role === 'guru' ? '/dashboard/guru' : '/dashboard/admin';
  dashboard.value = await useApi<DashboardResponse>(endpoint, { method: 'GET' });
};

onMounted(async () => {
  await fetchDashboard();
});
</script>
