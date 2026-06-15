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
                placeholder="Cari nama kelas atau rombel..."
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white lg:max-w-xs"
              />
            </label>
          </div>

          <div class="flex flex-wrap gap-3 lg:justify-end">
            <button v-if="auth.user?.role !== 'guru'" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="isFilterModalOpen = true">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3 5h18v2l-7 7v5l-4-2v-3L3 7V5Z" />
              </svg>
              Filter
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="fetchClassrooms">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink v-if="auth.user?.role !== 'guru'" to="/kelas/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Kelas
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="activeFilterLabel" class="border-b border-slate-100 px-5 py-4 text-sm font-medium text-indigo-500">
        {{ activeFilterLabel }}
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data kelas...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>
      <div v-else class="overflow-x-auto px-5 py-5">
        <table class="min-w-full text-sm">
          <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
            <tr>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nama_kelas')">
                  <span>Kelas</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nama_kelas') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('tingkat')">
                  <span>Tingkat</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('tingkat') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('rombel')">
                  <span>Rombel</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('rombel') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('wali_kelas')">
                  <span>Wali Kelas</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('wali_kelas') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('tahun_ajaran')">
                  <span>Tahun Ajaran</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('tahun_ajaran') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">Jumlah Siswa</th>
              <th v-if="auth.user?.role !== 'guru'" class="px-4 py-4 font-medium text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="classroom in classrooms.data" :key="classroom.id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M4 6.75A2.75 2.75 0 0 1 6.75 4h10.5A2.75 2.75 0 0 1 20 6.75v10.5A2.75 2.75 0 0 1 17.25 20H6.75A2.75 2.75 0 0 1 4 17.25V6.75Zm3 1.25a.75.75 0 0 0-.75.75v1.5c0 .414.336.75.75.75h10a.75.75 0 0 0 .75-.75v-1.5a.75.75 0 0 0-.75-.75H7Zm0 5a.75.75 0 0 0-.75.75v3.25c0 .414.336.75.75.75h3.25a.75.75 0 0 0 .75-.75v-3.25a.75.75 0 0 0-.75-.75H7Zm6 0a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-4Zm0 3a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5h-4Z" />
                    </svg>
                  </span>
                  <div>
                    <p class="font-semibold text-slate-900">{{ classroom.nama_kelas }}</p>
                    <p class="text-xs text-slate-400">ID #{{ classroom.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-slate-600">{{ classroom.tingkat }}</td>
              <td class="px-4 py-4 text-slate-600">{{ classroom.rombel }}</td>
              <td class="px-4 py-4 text-slate-600">{{ classroom.wali_kelas?.nama || '-' }}</td>
              <td class="px-4 py-4 text-slate-600">{{ formatAcademicYear(classroom) }}</td>
              <td class="px-4 py-4 text-slate-600">{{ classroom.jumlah_siswa ?? 0 }}</td>
              <td v-if="auth.user?.role !== 'guru'" class="px-4 py-4">
                <div class="flex items-center justify-end gap-2">
                  <NuxtLink :to="`/kelas/${classroom.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Edit kelas">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="m3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.25H5v-.92l8.06-8.06.92.92L5.92 19.5ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.13 1.13 3.75 3.75 1.14-1.13Z" />
                    </svg>
                  </NuxtLink>
                  <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="actionLoadingId === classroom.id"
                    :aria-label="actionLoadingId === classroom.id ? 'Menghapus kelas' : 'Hapus kelas'"
                    @click="deleteClassroom(classroom)"
                  >
                    <svg v-if="actionLoadingId !== classroom.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
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
            <tr v-if="!classrooms.data.length">
              <td :colspan="auth.user?.role === 'guru' ? 6 : 7" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data kelas yang cocok.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>
          Menampilkan {{ classrooms.meta.from || 0 }}-{{ classrooms.meta.to || 0 }} dari {{ classrooms.meta.total }} data
        </p>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-xl border border-slate-200 px-3 py-2 font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="classrooms.meta.current_page <= 1 || pending"
            @click="page -= 1"
          >
            Prev
          </button>
          <span class="px-2 font-semibold text-slate-700">Page {{ classrooms.meta.current_page }} / {{ classrooms.meta.last_page }}</span>
          <button
            type="button"
            class="rounded-xl border border-slate-200 px-3 py-2 font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="classrooms.meta.current_page >= classrooms.meta.last_page || pending"
            @click="page += 1"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <div v-if="isFilterModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4">
      <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Filter Kelas</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih filter untuk mempersempit data kelas.</p>
          </div>
          <button type="button" class="text-slate-400 transition hover:text-slate-600" @click="closeFilterModal">Ã¢Å“â€¢</button>
        </div>

        <div class="mt-6 space-y-4">
          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Tingkat</span>
            <select v-model="draftTingkat" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua tingkat</option>
              <option v-for="level in availableLevels" :key="level" :value="String(level)">
                Tingkat {{ level }}
              </option>
            </select>
          </label>

          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Tahun Ajaran</span>
            <select v-model="draftAcademicYearId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua tahun ajaran</option>
              <option v-for="academicYear in academicYears" :key="academicYear.id" :value="String(academicYear.id)">
                {{ academicYear.nama_tahun_ajaran }}  -  {{ academicYear.semester }}
              </option>
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
import type { AcademicYearOption, ClassroomItem, PaginatedResponse } from '~/types/classroom';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Data Kelas',
  pageDescription: 'Kelola daftar kelas, pencarian, filter, dan aksi data kelas.',
});

const auth = useAuthStore();
const toast = useToast();
const { confirm } = useConfirm();

const search = ref('');
const tingkat = ref('');
const academicYearId = ref('');
const page = ref(1);
const sortBy = ref<'nama_kelas' | 'tingkat' | 'rombel' | 'wali_kelas' | 'tahun_ajaran'>('nama_kelas');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pending = ref(false);
const errorMessage = ref('');
const isFilterModalOpen = ref(false);
const actionLoadingId = ref<number | null>(null);
const draftTingkat = ref('');
const draftAcademicYearId = ref('');

const emptyState: PaginatedResponse<ClassroomItem> = {
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

const classrooms = ref<PaginatedResponse<ClassroomItem>>(emptyState);
const academicYears = ref<AcademicYearOption[]>([]);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchClassrooms = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    classrooms.value = await useApi<PaginatedResponse<ClassroomItem>>('/classes', {
      method: 'GET',
      query: {
        page: page.value,
        per_page: 10,
        search: search.value || undefined,
        tingkat: tingkat.value || undefined,
        tahun_ajaran_id: academicYearId.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data kelas.';
  } finally {
    pending.value = false;
  }
};

const fetchAcademicYears = async () => {
  if (auth.user?.role === 'guru') {
    academicYears.value = [];
    return;
  }

  try {
    const response = await useApi<{ data: AcademicYearOption[] }>('/academic-years', { method: 'GET' });
    academicYears.value = response.data;
  } catch {
  }
};

const closeFilterModal = () => {
  isFilterModalOpen.value = false;
  draftTingkat.value = tingkat.value;
  draftAcademicYearId.value = academicYearId.value;
};

const applyFilters = async () => {
  tingkat.value = draftTingkat.value;
  academicYearId.value = draftAcademicYearId.value;
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchClassrooms();
};

const resetFilters = async () => {
  draftTingkat.value = '';
  draftAcademicYearId.value = '';
  tingkat.value = '';
  academicYearId.value = '';
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchClassrooms();
};

const toggleSort = async (column: 'nama_kelas' | 'tingkat' | 'rombel' | 'wali_kelas' | 'tahun_ajaran') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }

  page.value = 1;
  await fetchClassrooms();
};

const getSortIcon = (column: 'nama_kelas' | 'tingkat' | 'rombel' | 'wali_kelas' | 'tahun_ajaran') => {
  if (sortBy.value !== column) {
    return '↕';
  }

  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const deleteClassroom = async (classroom: ClassroomItem) => {
  const confirmed = await confirm({
    title: 'Hapus Data Kelas',
    message: `Yakin ingin menghapus data kelas ${classroom.nama_kelas}? Tindakan ini tidak dapat dibatalkan.`,
  });

  if (!confirmed) {
    return;
  }

  actionLoadingId.value = classroom.id;

  try {
    await useApi(`/classes/${classroom.id}`, { method: 'DELETE' });

    toast.success(`Data kelas ${classroom.nama_kelas} berhasil dihapus.`);

    if (classrooms.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchClassrooms();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus data kelas.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, fetchClassrooms, { immediate: true });

watch(search, () => {
  page.value = 1;

  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }

  debounceHandle = setTimeout(fetchClassrooms, 400);
});

onMounted(async () => {
  if (auth.user?.role !== 'guru') {
    await fetchAcademicYears();
  }
  draftTingkat.value = tingkat.value;
  draftAcademicYearId.value = academicYearId.value;
});

const availableLevels = computed(() => Array.from({ length: 6 }, (_, index) => index + 7));

const activeFilterLabel = computed(() => {
  const labels: string[] = [];

  if (tingkat.value) {
    labels.push(`Tingkat: ${tingkat.value}`);
  }

  if (academicYearId.value) {
    const academicYear = academicYears.value.find((item) => String(item.id) === academicYearId.value);

    if (academicYear) {
      labels.push(`Tahun Ajaran: ${academicYear.nama_tahun_ajaran} ${academicYear.semester}`);
    }
  }

  return labels.join(' | ');
});

const formatAcademicYear = (classroom: ClassroomItem) => {
  if (!classroom.tahun_ajaran) {
    return '-';
  }

  return `${classroom.tahun_ajaran.nama_tahun_ajaran}  -  ${classroom.tahun_ajaran.semester}`;
};
</script>
