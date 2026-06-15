<template>
  <div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="flex flex-col gap-5 border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex-1">
            <label class="relative block">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M10 4a6 6 0 1 0 3.874 10.582l4.272 4.272 1.414-1.414-4.272-4.272A6 6 0 0 0 10 4Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z" />
                </svg>
              </span>
              <input
                v-model="search"
                type="text"
                placeholder="Cari tahun ajaran..."
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white lg:max-w-xs"
              />
            </label>
          </div>

          <div class="flex flex-wrap gap-3 lg:justify-end">
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="isFilterModalOpen = true">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3 5h18v2l-7 7v5l-4-2v-3L3 7V5Z" />
              </svg>
              Filter
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="fetchAcademicYears">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink to="/tahun-ajaran/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Tahun Ajaran
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="activeFilterLabel" class="border-b border-slate-100 px-5 py-4 text-sm font-medium text-indigo-500">
        {{ activeFilterLabel }}
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data tahun ajaran...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>
      <div v-else class="overflow-x-auto px-5 py-5">
        <table class="min-w-full text-sm">
          <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
            <tr>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nama_tahun_ajaran')">
                  <span>Tahun Ajaran</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nama_tahun_ajaran') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('semester')">
                  <span>Semester</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('semester') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('status_aktif')">
                  <span>Status</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('status_aktif') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">Jumlah Kelas</th>
              <th class="px-4 py-4 font-medium text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="academicYear in academicYears.data" :key="academicYear.id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm14 9v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8h18Z" />
                    </svg>
                  </span>
                  <div>
                    <p class="font-semibold text-slate-900">{{ academicYear.nama_tahun_ajaran }}</p>
                    <p class="text-xs text-slate-400">ID #{{ academicYear.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-slate-600">{{ academicYear.semester }}</td>
              <td class="px-4 py-4">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="academicYear.status_aktif ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                  {{ academicYear.status_aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-4 text-slate-600">{{ academicYear.jumlah_kelas ?? 0 }}</td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="actionLoadingId === academicYear.id || academicYear.status_aktif"
                    aria-label="Aktifkan tahun ajaran"
                    @click="activateAcademicYear(academicYear)"
                  >
                    <svg v-if="actionLoadingId !== academicYear.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M12 2a10 10 0 1 0 10 10A10.012 10.012 0 0 0 12 2Zm-1 14-4-4 1.41-1.41L11 13.17l5.59-5.58L18 9Z" />
                    </svg>
                    <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" />
                      <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                  </button>
                  <NuxtLink :to="`/tahun-ajaran/${academicYear.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Edit tahun ajaran">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="m3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.25H5v-.92l8.06-8.06.92.92L5.92 19.5ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.13 1.13 3.75 3.75 1.14-1.13Z" />
                    </svg>
                  </NuxtLink>
                  <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="actionLoadingId === academicYear.id"
                    aria-label="Hapus tahun ajaran"
                    @click="deleteAcademicYear(academicYear)"
                  >
                    <svg v-if="actionLoadingId !== academicYear.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M9 3a1 1 0 0 0-1 1v1H5a1 1 0 1 0 0 2h1l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12h1a1 1 0 1 0 0-2h-3V4a1 1 0 0 0-1-1H9Zm2 2h2v1h-2V5Zm-1 5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm5 1a1 1 0 1 0-2 0v6a1 1 0 1 0 2 0v-6Z" />
                    </svg>
                    <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" />
                      <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!academicYears.data.length">
              <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data tahun ajaran yang cocok.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Menampilkan {{ academicYears.meta.from || 0 }}-{{ academicYears.meta.to || 0 }} dari {{ academicYears.meta.total }} data</p>
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="academicYears.meta.current_page <= 1 || pending" @click="page--">Prev</button>
          <span class="px-2 font-semibold text-slate-700">Page {{ academicYears.meta.current_page }} / {{ academicYears.meta.last_page }}</span>
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="academicYears.meta.current_page >= academicYears.meta.last_page || pending" @click="page++">Next</button>
        </div>
      </div>
    </div>

    <div v-if="isFilterModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4">
      <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Filter Tahun Ajaran</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih filter untuk mempersempit data tahun ajaran.</p>
          </div>
          <button type="button" class="text-slate-400 transition hover:text-slate-600" @click="closeFilterModal">✕</button>
        </div>

        <div class="mt-6 space-y-4">
          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Semester</span>
            <select v-model="draftSemester" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua semester</option>
              <option value="Ganjil">Ganjil</option>
              <option value="Genap">Genap</option>
            </select>
          </label>

          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Status</span>
            <select v-model="draftStatusAktif" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua status</option>
              <option value="true">Aktif</option>
              <option value="false">Nonaktif</option>
            </select>
          </label>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="resetFilters">
            Reset
          </button>
          <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200" @click="applyFilters">
            Terapkan Filter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { AcademicYearItem, PaginatedResponse } from '~/types/academic-year';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Tahun Ajaran',
  pageDescription: 'Kelola daftar tahun ajaran, status aktif, pencarian, filter, dan aksi data.',
});

const toast = useToast();
const { confirm } = useConfirm();
const search = ref('');
const semester = ref('');
const statusAktif = ref('');
const page = ref(1);
const sortBy = ref<'nama_tahun_ajaran' | 'semester' | 'status_aktif'>('status_aktif');
const sortDirection = ref<'asc' | 'desc'>('desc');
const pending = ref(false);
const errorMessage = ref('');
const isFilterModalOpen = ref(false);
const actionLoadingId = ref<number | null>(null);
const draftSemester = ref('');
const draftStatusAktif = ref('');

const emptyState: PaginatedResponse<AcademicYearItem> = {
  data: [],
  links: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 1,
    path: '',
    per_page: 10,
    to: null,
    total: 0,
  },
};

const academicYears = ref<PaginatedResponse<AcademicYearItem>>(emptyState);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchAcademicYears = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    academicYears.value = await useApi<PaginatedResponse<AcademicYearItem>>('/academic-years', {
      method: 'GET',
      query: {
        page: page.value,
        per_page: 10,
        search: search.value || undefined,
        semester: semester.value || undefined,
        status_aktif: statusAktif.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data tahun ajaran.';
  } finally {
    pending.value = false;
  }
};

const closeFilterModal = () => {
  isFilterModalOpen.value = false;
  draftSemester.value = semester.value;
  draftStatusAktif.value = statusAktif.value;
};

const applyFilters = async () => {
  semester.value = draftSemester.value;
  statusAktif.value = draftStatusAktif.value;
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchAcademicYears();
};

const resetFilters = async () => {
  draftSemester.value = '';
  draftStatusAktif.value = '';
  semester.value = '';
  statusAktif.value = '';
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchAcademicYears();
};

const toggleSort = async (column: 'nama_tahun_ajaran' | 'semester' | 'status_aktif') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = column === 'status_aktif' ? 'desc' : 'asc';
  }

  page.value = 1;
  await fetchAcademicYears();
};

const getSortIcon = (column: 'nama_tahun_ajaran' | 'semester' | 'status_aktif') => {
  if (sortBy.value !== column) {
    return '↕';
  }

  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const activateAcademicYear = async (academicYear: AcademicYearItem) => {
  if (academicYear.status_aktif) {
    return;
  }

  actionLoadingId.value = academicYear.id;

  try {
    await useApi(`/academic-years/${academicYear.id}`, {
      method: 'PUT',
      body: {
        nama_tahun_ajaran: academicYear.nama_tahun_ajaran,
        semester: academicYear.semester,
        status_aktif: true,
      },
    });

    toast.success(`Tahun ajaran ${academicYear.nama_tahun_ajaran} ${academicYear.semester} berhasil diaktifkan.`);
    await fetchAcademicYears();
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal mengaktifkan tahun ajaran.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

const deleteAcademicYear = async (academicYear: AcademicYearItem) => {
  const confirmed = await confirm({
    title: 'Hapus Tahun Ajaran',
    message: `Yakin ingin menghapus data tahun ajaran ${academicYear.nama_tahun_ajaran} ${academicYear.semester}? Tindakan ini tidak dapat dibatalkan.`,
  });

  if (!confirmed) {
    return;
  }

  actionLoadingId.value = academicYear.id;

  try {
    await useApi(`/academic-years/${academicYear.id}`, { method: 'DELETE' });

    toast.success(`Tahun ajaran ${academicYear.nama_tahun_ajaran} ${academicYear.semester} berhasil dihapus.`);

    if (academicYears.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchAcademicYears();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus data tahun ajaran.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, fetchAcademicYears, { immediate: true });

watch(search, () => {
  page.value = 1;

  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }

  debounceHandle = setTimeout(fetchAcademicYears, 400);
});

onMounted(() => {
  draftSemester.value = semester.value;
  draftStatusAktif.value = statusAktif.value;
});

const activeFilterLabel = computed(() => {
  const labels: string[] = [];

  if (semester.value) {
    labels.push(`Semester: ${semester.value}`);
  }

  if (statusAktif.value === 'true') {
    labels.push('Status: Aktif');
  } else if (statusAktif.value === 'false') {
    labels.push('Status: Nonaktif');
  }

  return labels.join(' • ');
});
</script>
