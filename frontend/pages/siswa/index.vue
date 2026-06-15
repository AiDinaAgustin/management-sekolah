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
                placeholder="Cari nama atau NIS..."
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
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="fetchStudents">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink to="/siswa/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Siswa
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="activeFilterLabel" class="border-b border-slate-100 px-5 py-4 text-sm font-medium text-indigo-500">
        {{ activeFilterLabel }}
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data siswa...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>
      <div v-else class="overflow-x-auto px-5 py-5">
        <table class="min-w-full text-sm">
          <thead class="border-b border-slate-200 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
            <tr>
              <th class="px-4 py-4 font-medium">Photo</th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nama_lengkap')">
                  <span>Member name</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nama_lengkap') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">NIS</th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('jenis_kelamin')">
                  <span>Jenis Kelamin</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('jenis_kelamin') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('tanggal_lahir')">
                  <span>Tanggal Lahir</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('tanggal_lahir') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('kelas')">
                  <span>Kelas</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('kelas') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">Status</th>
              <th class="px-4 py-4 font-medium">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80">
            <tr v-for="student in students.data" :key="student.id" class="group hover:bg-indigo-50/40">
              <td class="px-4 py-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-white shadow-md shadow-indigo-100">
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8ZM3 16a5 5 0 015-5h4a5 5 0 015 5v1H3v-1Z" clip-rule="evenodd" />
                  </svg>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="font-semibold text-slate-800">{{ student.nama_lengkap }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ student.tempat_lahir || '-' }}</div>
              </td>
              <td class="px-4 py-4 font-medium text-slate-600">{{ student.nis }}</td>
              <td class="px-4 py-4 text-slate-600">{{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              <td class="px-4 py-4 text-slate-600">{{ student.tanggal_lahir ? formatDate(student.tanggal_lahir) : '-' }}</td>
              <td class="px-4 py-4 text-slate-600">{{ student.kelas?.nama_kelas || '-' }}</td>
              <td class="px-4 py-4">
                <span :class="student.status_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600'" class="rounded-full px-3 py-1 text-xs font-semibold">
                  {{ student.status_aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center gap-2">
                  <NuxtLink :to="`/siswa/${student.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z" />
                    </svg>
                  </NuxtLink>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" :disabled="actionLoadingId === student.id" @click="deleteStudent(student)">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M9 3h6l1 2h5v2H3V5h5l1-2Zm1 7h2v8h-2v-8Zm4 0h2v8h-2v-8ZM7 10h2v8H7v-8Z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="students.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada data siswa.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-slate-100 px-5 py-4 text-sm text-slate-500">
        <span>Total {{ students.meta.total }} siswa</span>
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="students.meta.current_page <= 1 || pending" @click="page--">
            Prev
          </button>
          <span>Halaman {{ students.meta.current_page }} / {{ students.meta.last_page }}</span>
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="students.meta.current_page >= students.meta.last_page || pending" @click="page++">
            Next
          </button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="isFilterModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm" @click.self="closeFilterModal">
        <div class="w-full max-w-lg rounded-[2rem] border border-white/80 bg-white p-6 shadow-[0_25px_70px_rgba(15,23,42,0.2)]">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-indigo-500">Filter</p>
              <h3 class="mt-2 text-2xl font-bold text-slate-900">Atur Filter Data Siswa</h3>
              <p class="text-sm text-slate-500">Pilih kriteria filter yang ingin ditampilkan.</p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-400 transition hover:bg-slate-50 hover:text-slate-600" @click="closeFilterModal">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="m18.3 5.71-1.41-1.42L12 9.17 7.11 4.29 5.7 5.71 10.59 10.6 5.7 15.49l1.41 1.42L12 12l4.89 4.91 1.41-1.42-4.89-4.89 4.89-4.89Z" />
              </svg>
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <label class="block space-y-2">
              <span class="text-sm font-medium text-slate-700">Status siswa</span>
              <select v-model="draftStatusAktif" class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white">
                <option value="">Semua status</option>
                <option value="true">Aktif</option>
                <option value="false">Nonaktif</option>
              </select>
            </label>

            <label class="block space-y-2">
              <span class="text-sm font-medium text-slate-700">Kelas</span>
              <select v-model="draftKelasId" class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white">
                <option value="">Semua kelas</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="String(classroom.id)">
                  {{ classroom.nama_kelas }}
                </option>
              </select>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600" @click="resetFilters">
              Reset
            </button>
            <button type="button" class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200" @click="applyFilters">
              Terapkan Filter
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import type { ClassroomOption, PaginatedResponse, StudentItem } from '~/types/student';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Data Siswa',
  pageDescription: 'Kelola daftar siswa, pencarian, filter, dan aksi data siswa.',
});

const toast = useToast();
const { confirm } = useConfirm();
const search = ref('');
const statusAktif = ref('');
const kelasId = ref('');
const page = ref(1);
const sortBy = ref<'nama_lengkap' | 'jenis_kelamin' | 'tanggal_lahir' | 'kelas'>('nama_lengkap');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pending = ref(false);
const errorMessage = ref('');
const isFilterModalOpen = ref(false);
const actionLoadingId = ref<number | null>(null);
const draftStatusAktif = ref('');
const draftKelasId = ref('');

const emptyState: PaginatedResponse<StudentItem> = {
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

const students = ref<PaginatedResponse<StudentItem>>(emptyState);
const classrooms = ref<ClassroomOption[]>([]);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchStudents = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    students.value = await useApi<PaginatedResponse<StudentItem>>('/students', {
      method: 'GET',
      query: {
        page: page.value,
        per_page: 10,
        search: search.value || undefined,
        status_aktif: statusAktif.value || undefined,
        kelas_id: kelasId.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data siswa.';
  } finally {
    pending.value = false;
  }
};

const fetchClassrooms = async () => {
  try {
    const response = await useApi<{ data: ClassroomOption[] }>('/classes', {
      method: 'GET',
      query: { simple: true },
    });

    classrooms.value = response.data;
  } catch {
  }
};

const closeFilterModal = () => {
  isFilterModalOpen.value = false;
  draftStatusAktif.value = statusAktif.value;
  draftKelasId.value = kelasId.value;
};

const applyFilters = async () => {
  statusAktif.value = draftStatusAktif.value;
  kelasId.value = draftKelasId.value;
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchStudents();
};

const resetFilters = async () => {
  draftStatusAktif.value = '';
  draftKelasId.value = '';
  statusAktif.value = '';
  kelasId.value = '';
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchStudents();
};

const toggleSort = async (column: 'nama_lengkap' | 'jenis_kelamin' | 'tanggal_lahir' | 'kelas') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }

  page.value = 1;
  await fetchStudents();
};

const getSortIcon = (column: 'nama_lengkap' | 'jenis_kelamin' | 'tanggal_lahir' | 'kelas') => {
  if (sortBy.value !== column) {
    return '↕';
  }

  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const deleteStudent = async (student: StudentItem) => {
  const confirmed = await confirm({
    title: 'Hapus Data Siswa',
    message: `Yakin ingin menghapus data siswa ${student.nama_lengkap}? Tindakan ini tidak dapat dibatalkan.`,
  });

  if (!confirmed) {
    return;
  }

  actionLoadingId.value = student.id;

  try {
    await useApi(`/students/${student.id}`, {
      method: 'DELETE',
    });

    toast.success(`Data siswa ${student.nama_lengkap} berhasil dihapus.`);

    if (students.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchStudents();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus data siswa.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, fetchStudents, { immediate: true });

watch(search, () => {
  page.value = 1;

  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }

  debounceHandle = setTimeout(fetchStudents, 400);
});

onMounted(async () => {
  await fetchClassrooms();
  draftStatusAktif.value = statusAktif.value;
  draftKelasId.value = kelasId.value;
});

const activeFilterLabel = computed(() => {
  const labels: string[] = [];

  if (statusAktif.value === 'true') {
    labels.push('Status: Aktif');
  } else if (statusAktif.value === 'false') {
    labels.push('Status: Nonaktif');
  }

  if (kelasId.value) {
    const classroom = classrooms.value.find((item) => String(item.id) === kelasId.value);

    if (classroom) {
      labels.push(`Kelas: ${classroom.nama_kelas}`);
    }
  }

  return labels.join(' • ');
});

const formatDate = (date: string) => {
  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return date;
  }

  return parsedDate.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};
</script>
