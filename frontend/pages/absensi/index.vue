<template>
  <div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="flex flex-col gap-5 border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex-1 space-y-3">
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="viewMode === 'input' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
                @click="viewMode = 'input'"
              >
                Input Absensi
              </button>
              <button
                type="button"
                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="viewMode === 'rekap' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
                @click="switchToRecap"
              >
                Rekap Absensi
              </button>
            </div>

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-600">Tanggal</span>
                <input v-model="selectedDate" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 lg:w-52" />
              </label>

              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-600">Kelas</span>
                <select v-model="selectedClassroomId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 lg:w-60">
                  <option value="">Pilih kelas</option>
                  <option v-for="classroom in classrooms" :key="classroom.id" :value="String(classroom.id)">
                    {{ classroom.nama_kelas }}
                  </option>
                </select>
              </label>

              <label v-if="auth.user?.role === 'admin'" class="block">
                <span class="mb-2 block text-sm font-medium text-slate-600">Guru Penginput</span>
                <select v-model="selectedTeacherId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 lg:w-64">
                  <option value="">Pilih guru</option>
                  <option v-for="teacher in teachers" :key="teacher.id" :value="String(teacher.id)">
                    {{ teacher.nama }} · {{ teacher.nip }}
                  </option>
                </select>
              </label>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 xl:justify-end">
            <button
              v-if="viewMode === 'input'"
              type="button"
              class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50"
              @click="loadAttendanceSheet"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Muat Data
            </button>
            <button
              v-if="viewMode === 'input'"
              type="button"
              class="inline-flex items-center gap-2 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-600 transition hover:border-emerald-400 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="!attendanceRows.length"
              @click="setAllHadir"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M9.55 18.2 3.9 12.55l1.4-1.4 4.25 4.25 9.15-9.15 1.4 1.4Z" />
              </svg>
              Set Semua Hadir
            </button>
            <button
              v-if="viewMode === 'rekap'"
              type="button"
              class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50"
              @click="fetchRecap"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh Rekap
            </button>
            <button
              v-if="viewMode === 'input'"
              type="button"
              class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70"
              :disabled="isSubmitting || !attendanceRows.length"
              @click="submitAttendance"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17 3a1 1 0 0 1 .993.883L18 4v16a1 1 0 0 1-1.993.117L16 20V4a1 1 0 0 1 1-1ZM7 7a1 1 0 0 1 .993.883L8 8v12a1 1 0 0 1-1.993.117L6 20V8a1 1 0 0 1 1-1Zm5-2a1 1 0 0 1 .993.883L13 6v14a1 1 0 0 1-1.993.117L11 20V6a1 1 0 0 1 1-1Z" />
              </svg>
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Absensi' }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-4 text-sm text-rose-600">
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="border-b border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-600">
        {{ successMessage }}
      </div>

      <template v-if="viewMode === 'input'">
        <div v-if="isLoadingSheet" class="p-6 text-sm text-slate-500">Memuat lembar absensi...</div>
        <div v-else class="px-5 py-5">
          <div v-if="!selectedClassroomId" class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-400">
            Pilih kelas dulu untuk memulai input absensi harian.
          </div>
          <div v-else-if="!attendanceRows.length" class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-400">
            Belum ada siswa aktif di kelas ini atau data belum dimuat.
          </div>
        <div v-else class="overflow-x-auto rounded-[1.5rem] border border-slate-300">
          <div class="border-b border-slate-300 bg-slate-50 px-5 py-3 text-sm text-slate-500">
            Status hanya terisi jika absensi pada tanggal ini sudah pernah disimpan.
          </div>
          <table class="min-w-full text-sm">
              <thead class="border-b border-slate-300 bg-slate-50 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
                <tr>
                  <th class="w-16 px-4 py-4 text-center font-medium">No</th>
                  <th class="w-36 px-4 py-4 font-medium">NIS</th>
                  <th class="w-52 px-4 py-4 font-medium">Nama Siswa</th>
                  <th class="w-52 px-4 py-4 font-medium">Jenis Kelamin</th>
                  <th class="min-w-[260px] px-4 py-4 font-medium">Status</th>
                  <th class="min-w-[280px] px-4 py-4 font-medium">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in attendanceRows" :key="row.siswa_id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
                  <td class="px-4 py-4 text-center font-semibold text-slate-500">{{ index + 1 }}</td>
                  <td class="px-4 py-4 font-medium text-slate-500">{{ row.nis }}</td>
                  <td class="px-4 py-4">
                    <div>
                      <p class="font-semibold text-slate-900">{{ row.nama_lengkap }}</p>
                    </div>
                  </td>
                  <td class="px-4 py-4 font-medium text-slate-500">{{ row.jenis_kelamin }}</td>
                  <td class="px-4 py-4">
                    <div class="flex flex-wrap gap-2">
                      <button
                        v-for="status in attendanceStatuses"
                        :key="status.value"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                        :class="row.status === status.value ? status.activeClass : 'border-slate-300 bg-white text-slate-400 hover:border-indigo-300 hover:text-indigo-600'"
                        @click="row.status = status.value"
                      >
                        {{ status.label }}
                      </button>
                    </div>
                  </td>
                  <td class="px-4 py-4">
                    <input v-model="row.keterangan" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Opsional" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <template v-else>
        <div v-if="isLoadingRecap" class="p-6 text-sm text-slate-500">Memuat rekap absensi...</div>
        <div v-else class="space-y-5 px-5 py-5">
          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="card in recapCards" :key="card.label" class="rounded-[1.5rem] border border-slate-300 bg-white p-5 shadow-sm">
              <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
              <p class="mt-2 text-2xl font-bold" :class="card.valueClass">{{ card.value }}</p>
            </div>
          </div>

          <div class="overflow-x-auto rounded-[1.5rem] border border-slate-300">
            <table class="min-w-full text-sm">
              <thead class="border-b border-slate-300 bg-slate-50 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
                <tr>
                  <th class="px-4 py-4 font-medium">NIS</th>
                  <th class="px-4 py-4 font-medium">Nama Siswa</th>
                  <th class="px-4 py-4 font-medium">Kelas</th>
                  <th class="px-4 py-4 font-medium">Hadir</th>
                  <th class="px-4 py-4 font-medium">Izin</th>
                  <th class="px-4 py-4 font-medium">Sakit</th>
                  <th class="px-4 py-4 font-medium">Alfa</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="student in recap.students" :key="student.siswa_id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
                  <td class="px-4 py-4 text-slate-500">{{ student.student?.nis || '-' }}</td>
                  <td class="px-4 py-4 font-semibold text-slate-900">{{ student.student?.nama_lengkap || '-' }}</td>
                  <td class="px-4 py-4 text-slate-600">{{ student.student?.kelas?.nama_kelas || '-' }}</td>
                  <td class="px-4 py-4 font-semibold text-emerald-600">{{ student.hadir }}</td>
                  <td class="px-4 py-4 font-semibold text-amber-500">{{ student.izin }}</td>
                  <td class="px-4 py-4 font-semibold text-sky-500">{{ student.sakit }}</td>
                  <td class="px-4 py-4 font-semibold text-rose-500">{{ student.alfa }}</td>
                </tr>
                <tr v-if="!recap.students.length">
                  <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data rekap untuk filter yang dipilih.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { AttendanceItem, AttendanceRecapResponse, ClassroomOption, PaginatedResponse, StudentOption, TeacherOption } from '~/types/attendance';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Absensi Harian',
  pageDescription: 'Input absensi harian siswa dan lihat rekap kehadiran per kelas.',
});

type AttendanceRow = {
  siswa_id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  status: 'hadir' | 'izin' | 'sakit' | 'alfa' | null;
  keterangan: string;
};

const auth = useAuthStore();
const toast = useToast();
const viewMode = ref<'input' | 'rekap'>('input');
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const selectedClassroomId = ref('');
const selectedTeacherId = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const isLoadingSheet = ref(false);
const isLoadingRecap = ref(false);
const isSubmitting = ref(false);
const classrooms = ref<ClassroomOption[]>([]);
const teachers = ref<TeacherOption[]>([]);
const attendanceRows = ref<AttendanceRow[]>([]);
const recap = ref<AttendanceRecapResponse>({
  summary: { hadir: 0, izin: 0, sakit: 0, alfa: 0 },
  students: [],
});

const attendanceStatuses = [
  { value: 'hadir', label: 'Hadir', activeClass: 'border-emerald-200 bg-emerald-50 text-emerald-600' },
  { value: 'izin', label: 'Izin', activeClass: 'border-amber-200 bg-amber-50 text-amber-600' },
  { value: 'sakit', label: 'Sakit', activeClass: 'border-sky-200 bg-sky-50 text-sky-600' },
  { value: 'alfa', label: 'Alfa', activeClass: 'border-rose-200 bg-rose-50 text-rose-600' },
] as const;

const recapCards = computed(() => [
  { label: 'Total Hadir', value: recap.value.summary.hadir, valueClass: 'text-emerald-600' },
  { label: 'Total Izin', value: recap.value.summary.izin, valueClass: 'text-amber-500' },
  { label: 'Total Sakit', value: recap.value.summary.sakit, valueClass: 'text-sky-500' },
  { label: 'Total Alfa', value: recap.value.summary.alfa, valueClass: 'text-rose-500' },
]);

const fetchOptions = async () => {
  try {
    const requests = [
      useApi<{ data: ClassroomOption[] }>('/classes', { method: 'GET', query: { simple: true, for_attendance: true } }),
      auth.user?.role === 'admin'
        ? useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } })
        : Promise.resolve({ data: [] as TeacherOption[] }),
    ] as const;

    const [classroomsResponse, teachersResponse] = await Promise.all(requests);
    classrooms.value = classroomsResponse.data;
    teachers.value = teachersResponse.data;
  } catch {}
};

const loadAttendanceSheet = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (!selectedClassroomId.value) {
    errorMessage.value = 'Pilih kelas terlebih dahulu.';
    return;
  }

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    errorMessage.value = 'Pilih guru penginput terlebih dahulu.';
    return;
  }

  isLoadingSheet.value = true;

  try {
    const classroomStudents = await useApi<PaginatedResponse<StudentOption>>('/students', {
      method: 'GET',
      query: {
        kelas_id: selectedClassroomId.value,
        for_attendance: true,
        status_aktif: true,
        per_page: 100,
      },
    });

    const existingAttendances = await useApi<{ data: AttendanceItem[] }>('/attendances', {
      method: 'GET',
      query: {
        tanggal: selectedDate.value,
        kelas_id: selectedClassroomId.value,
        per_page: 100,
      },
    });

    const existingMap = new Map(existingAttendances.data.map((item) => [item.siswa_id, item]));

    attendanceRows.value = classroomStudents.data.map((student) => ({
      siswa_id: student.id,
      nis: student.nis,
      nama_lengkap: student.nama_lengkap,
      jenis_kelamin: student.jenis_kelamin,
      status: (existingMap.get(student.id)?.status || null) as AttendanceRow['status'],
      keterangan: existingMap.get(student.id)?.keterangan || '',
    }));
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat lembar absensi.';
  } finally {
    isLoadingSheet.value = false;
  }
};

const setAllHadir = () => {
  attendanceRows.value = attendanceRows.value.map((row) => ({
    ...row,
    status: 'hadir',
  }));
  errorMessage.value = '';
};

const submitAttendance = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (!selectedClassroomId.value || !attendanceRows.value.length) {
    errorMessage.value = 'Data absensi belum siap disimpan.';
    return;
  }

  if (attendanceRows.value.some((row) => !row.status)) {
    errorMessage.value = 'Semua siswa harus dipilih status absensinya terlebih dahulu.';
    return;
  }

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    errorMessage.value = 'Pilih guru penginput terlebih dahulu.';
    return;
  }

  isSubmitting.value = true;

  try {
    await useApi('/attendances/bulk', {
      method: 'POST',
      body: {
        tanggal: selectedDate.value,
        kelas_id: Number(selectedClassroomId.value),
        guru_id: auth.user?.role === 'admin' ? Number(selectedTeacherId.value) : undefined,
        attendances: attendanceRows.value.map((row) => ({
          siswa_id: row.siswa_id,
          status: row.status,
          keterangan: row.keterangan || null,
        })),
      },
    });

    successMessage.value = 'Absensi harian berhasil disimpan.';
    toast.success(successMessage.value);
    await loadAttendanceSheet();
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    errorMessage.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan absensi.';
    toast.error(errorMessage.value);
  } finally {
    isSubmitting.value = false;
  }
};

const fetchRecap = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  isLoadingRecap.value = true;

  try {
    recap.value = await useApi<AttendanceRecapResponse>('/attendances/recap', {
      method: 'GET',
      query: {
        kelas_id: selectedClassroomId.value || undefined,
        tanggal_mulai: `${selectedDate.value.slice(0, 7)}-01`,
        tanggal_selesai: selectedDate.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat rekap absensi.';
  } finally {
    isLoadingRecap.value = false;
  }
};

const switchToRecap = async () => {
  viewMode.value = 'rekap';
  await fetchRecap();
};

watch(selectedClassroomId, async () => {
  attendanceRows.value = [];

  if (viewMode.value !== 'input') {
    return;
  }

  if (!selectedClassroomId.value) {
    return;
  }

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    return;
  }

  await loadAttendanceSheet();
});

watch(selectedDate, async () => {
  if (viewMode.value !== 'input' || !selectedClassroomId.value) {
    return;
  }

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    return;
  }

  await loadAttendanceSheet();
});

watch(selectedTeacherId, async () => {
  if (viewMode.value !== 'input' || !selectedClassroomId.value) {
    return;
  }

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    return;
  }

  await loadAttendanceSheet();
});

onMounted(async () => {
  await fetchOptions();
});
</script>
