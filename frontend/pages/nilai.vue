<template>
  <div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <label v-if="auth.user?.role === 'admin'" class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Guru</span>
              <select v-model="selectedTeacherId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option value="">Pilih guru</option>
                <option v-for="teacher in teachers" :key="teacher.id" :value="String(teacher.id)">
                  {{ teacher.nama }} - {{ teacher.nip }}
                </option>
              </select>
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Kelas</span>
              <select v-model="selectedClassroomId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option value="">Pilih kelas</option>
                <option v-for="classroom in classrooms" :key="classroom.kelas?.id" :value="String(classroom.kelas?.id)">
                  {{ classroom.kelas?.nama_kelas }}
                </option>
              </select>
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Mata Pelajaran</span>
              <select v-model="selectedSubjectId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option value="">Pilih mapel</option>
                <option v-for="subject in availableSubjects" :key="subject.id" :value="String(subject.id)">
                  {{ subject.nama_mapel }} ({{ subject.kode_mapel }})
                </option>
              </select>
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Semester</span>
              <select v-model="selectedSemester" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option value="">Pilih semester</option>
                <option v-for="semester in semesters" :key="semester" :value="semester">
                  {{ semester }}
                </option>
              </select>
            </label>
          </div>

          <div class="flex flex-wrap gap-3">
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50" @click="fetchGradeSheet">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Muat Nilai
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSaving || !gradeRows.length" @click="saveGrades">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17 3H5a2 2 0 0 0-2 2v14l4-3h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z" />
              </svg>
              Simpan Nilai
            </button>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-4 text-sm text-rose-600">{{ errorMessage }}</div>
      <div v-else-if="successMessage" class="border-b border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-600">{{ successMessage }}</div>

      <div class="p-5">
        <div class="rounded-[1.75rem] border border-slate-300 bg-white shadow-sm">
          <div class="border-b border-slate-300 bg-slate-50 px-5 py-3 text-sm text-slate-500">
            Input nilai tugas, UTS, dan UAS per siswa. Nilai akhir dihitung otomatis dari rata-rata ketiganya.
          </div>

          <div class="border-b border-slate-300 px-5 py-4">
            <label class="relative block max-w-md">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M10 4a6 6 0 1 0 3.874 10.582l4.272 4.272 1.414-1.414-4.272-4.272A6 6 0 0 0 10 4Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z" />
                </svg>
              </span>
              <input v-model="studentSearch" type="text" placeholder="Cari NIS atau nama siswa..." class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300" />
            </label>
          </div>

          <div v-if="isLoading" class="p-6 text-sm text-slate-500">Memuat data nilai...</div>
          <div v-else class="max-h-[60vh] overflow-y-auto overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
                <tr>
                  <th class="px-4 py-4 font-medium">No</th>
                  <th class="px-4 py-4 font-medium">NIS</th>
                  <th class="px-4 py-4 font-medium">Nama Siswa</th>
                  <th class="px-4 py-4 font-medium">JK</th>
                  <th class="px-4 py-4 font-medium">Tugas</th>
                  <th class="px-4 py-4 font-medium">UTS</th>
                  <th class="px-4 py-4 font-medium">UAS</th>
                  <th class="px-4 py-4 font-medium">Nilai Akhir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in filteredGradeRows" :key="row.siswa_id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
                  <td class="px-4 py-4 text-center font-semibold text-slate-500">{{ index + 1 }}</td>
                  <td class="px-4 py-4 font-medium text-slate-500">{{ row.nis }}</td>
                  <td class="px-4 py-4 font-semibold text-slate-900">{{ row.nama_lengkap }}</td>
                  <td class="px-4 py-4 font-medium text-slate-500">{{ row.jenis_kelamin }}</td>
                  <td class="px-4 py-4"><input v-model.number="row.tugas" type="number" min="0" max="100" class="w-24 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" /></td>
                  <td class="px-4 py-4"><input v-model.number="row.uts" type="number" min="0" max="100" class="w-24 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" /></td>
                  <td class="px-4 py-4"><input v-model.number="row.uas" type="number" min="0" max="100" class="w-24 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" /></td>
                  <td class="px-4 py-4 font-semibold text-indigo-600">{{ calculateFinalScore(row) }}</td>
                </tr>
                <tr v-if="!filteredGradeRows.length">
                  <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">
                    {{ gradeRows.length ? 'Data siswa tidak ditemukan untuk pencarian ini.' : 'Pilih kelas, mapel, dan semester. Data nilai akan dimuat otomatis.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { GradeClassroomOption, GradeOptionsResponse, GradeStudentRow, TeacherOption } from '~/types/grade';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Input Nilai',
  pageDescription: 'Guru dapat input nilai tugas, UTS, dan UAS per siswa berdasarkan kelas dan mata pelajaran.',
});

type GradeRowForm = {
  siswa_id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  tugas: number;
  uts: number;
  uas: number;
};

const auth = useAuthStore();
const toast = useToast();
const teachers = ref<TeacherOption[]>([]);
const classrooms = ref<GradeClassroomOption[]>([]);
const semesters = ref<string[]>([]);
const selectedTeacherId = ref('');
const selectedClassroomId = ref('');
const selectedSubjectId = ref('');
const selectedSemester = ref('');
const studentSearch = ref('');
const gradeRows = ref<GradeRowForm[]>([]);
const isLoading = ref(false);
const isSaving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const availableSubjects = computed(() => {
  const selectedClassroom = classrooms.value.find((item) => String(item.kelas?.id) === selectedClassroomId.value);
  return selectedClassroom?.subjects || [];
});

const hasCompleteFilters = computed(() => Boolean(
  selectedClassroomId.value && selectedSubjectId.value && selectedSemester.value && (auth.user?.role !== 'admin' || selectedTeacherId.value),
));

const filteredGradeRows = computed(() => {
  const keyword = studentSearch.value.trim().toLowerCase();
  if (!keyword) return gradeRows.value;
  return gradeRows.value.filter((row) => row.nis.toLowerCase().includes(keyword) || row.nama_lengkap.toLowerCase().includes(keyword));
});

const fetchTeachers = async () => {
  if (auth.user?.role !== 'admin') {
    teachers.value = [];
    return;
  }
  try {
    const response = await useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } });
    teachers.value = response.data;
  } catch {}
};

const fetchOptions = async () => {
  errorMessage.value = '';
  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    classrooms.value = [];
    semesters.value = [];
    return;
  }
  try {
    const response = await useApi<{ data: GradeOptionsResponse }>('/grades/options', {
      method: 'GET',
      query: { teacher_id: auth.user?.role === 'admin' ? selectedTeacherId.value : undefined },
    });
    classrooms.value = response.data.classrooms;
    semesters.value = response.data.semesters;
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat opsi nilai.';
  }
};

const fetchGradeSheet = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  if (!selectedClassroomId.value || !selectedSubjectId.value || !selectedSemester.value) {
    errorMessage.value = 'Pilih kelas, mata pelajaran, dan semester terlebih dahulu.';
    return;
  }
  isLoading.value = true;
  try {
    const response = await useApi<{ data: GradeStudentRow[] }>('/grades', {
      method: 'GET',
      query: {
        kelas_id: selectedClassroomId.value,
        mapel_id: selectedSubjectId.value,
        semester: selectedSemester.value,
        teacher_id: auth.user?.role === 'admin' ? selectedTeacherId.value : undefined,
      },
    });
    gradeRows.value = response.data.map((row) => ({
      siswa_id: row.siswa_id,
      nis: row.nis,
      nama_lengkap: row.nama_lengkap,
      jenis_kelamin: row.jenis_kelamin,
      tugas: row.grade?.tugas ?? 0,
      uts: row.grade?.uts ?? 0,
      uas: row.grade?.uas ?? 0,
    }));
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data nilai.';
  } finally {
    isLoading.value = false;
  }
};

const calculateFinalScore = (row: GradeRowForm) => Number((((row.tugas || 0) + (row.uts || 0) + (row.uas || 0)) / 3).toFixed(2));

const saveGrades = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  if (!gradeRows.value.length) {
    errorMessage.value = 'Belum ada data nilai yang bisa disimpan.';
    return;
  }
  const invalidRow = gradeRows.value.some((row) => [row.tugas, row.uts, row.uas].some((score) => score < 0 || score > 100 || Number.isNaN(score)));
  if (invalidRow) {
    errorMessage.value = 'Nilai harus di antara 0 sampai 100.';
    return;
  }
  isSaving.value = true;
  try {
    await useApi('/grades', {
      method: 'POST',
      body: {
        kelas_id: Number(selectedClassroomId.value),
        mapel_id: Number(selectedSubjectId.value),
        semester: selectedSemester.value,
        teacher_id: auth.user?.role === 'admin' ? Number(selectedTeacherId.value) : undefined,
        grades: gradeRows.value.map((row) => ({
          siswa_id: row.siswa_id,
          tugas: row.tugas,
          uts: row.uts,
          uas: row.uas,
        })),
      },
    });
    successMessage.value = 'Nilai berhasil disimpan.';
    toast.success(successMessage.value);
    await fetchGradeSheet();
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    errorMessage.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan nilai.';
    toast.error(errorMessage.value);
  } finally {
    isSaving.value = false;
  }
};

watch(selectedTeacherId, async () => {
  selectedClassroomId.value = '';
  selectedSubjectId.value = '';
  selectedSemester.value = '';
  studentSearch.value = '';
  gradeRows.value = [];
  await fetchOptions();
});

watch(selectedClassroomId, () => {
  selectedSubjectId.value = '';
  studentSearch.value = '';
  gradeRows.value = [];
});

watch(selectedSubjectId, () => {
  studentSearch.value = '';
  gradeRows.value = [];
});

watch(selectedSemester, () => {
  studentSearch.value = '';
  gradeRows.value = [];
});

watch(() => [selectedTeacherId.value, selectedClassroomId.value, selectedSubjectId.value, selectedSemester.value], async () => {
  if (!hasCompleteFilters.value) return;
  await fetchGradeSheet();
});

onMounted(async () => {
  await fetchTeachers();
  if (auth.user?.role === 'guru') {
    await fetchOptions();
  }
});
</script>
