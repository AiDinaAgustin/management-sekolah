<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Jadwal Pelajaran Baru' : 'Edit Jadwal Pelajaran' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Atur kelas, waktu, guru, dan mapel tanpa bentrok.</p>
    </div>

    <div v-if="loadingSchedule" class="p-6 text-sm text-slate-500">Memuat data jadwal...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitSchedule">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Jadwal</h3>
          <p class="mt-1 text-sm text-slate-500">Pilih guru lalu sistem hanya menampilkan mapel yang memang diampu.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Tahun Ajaran</span>
            <select v-model="form.tahun_ajaran_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih tahun ajaran</option>
              <option v-for="academicYear in academicYears" :key="academicYear.id" :value="academicYear.id">
                {{ academicYear.nama_tahun_ajaran }} · {{ academicYear.semester }}{{ academicYear.status_aktif ? ' · Aktif' : '' }}
              </option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Kelas</span>
            <select v-model="form.kelas_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih kelas</option>
              <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                {{ classroom.nama_kelas }}
              </option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Hari</span>
            <select v-model="form.hari" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Guru</span>
            <select v-model="form.teacher_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih guru</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.nama }} · {{ teacher.nip }}
              </option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Mata Pelajaran</span>
            <select v-model="form.subject_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" :disabled="!filteredSubjects.length">
              <option :value="null">{{ filteredSubjects.length ? 'Pilih mata pelajaran' : 'Pilih guru dulu' }}</option>
              <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">
                {{ subject.nama_mapel }} · {{ subject.kode_mapel }}
              </option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Ruangan</span>
            <input v-model="form.ruangan" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Opsional" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Jam Mulai</span>
            <input v-model="form.jam_mulai" type="time" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Jam Selesai</span>
            <input v-model="form.jam_selesai" type="time" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" />
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/jadwal-pelajaran" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Jadwal' : 'Update Jadwal' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { AcademicYearOption, ClassroomOption, PaginatedResponse, ScheduleItem, SubjectOption, TeacherOption, TeacherSubjectItem } from '~/types/schedule';

const props = defineProps<{
  mode: 'create' | 'edit';
  scheduleId?: number | null;
}>();

const emit = defineEmits<{ saved: [] }>();

type ScheduleForm = {
  tahun_ajaran_id: number | null;
  kelas_id: number | null;
  teacher_id: number | null;
  subject_id: number | null;
  hari: 'Senin' | 'Selasa' | 'Rabu' | 'Kamis' | 'Jumat' | 'Sabtu';
  jam_mulai: string;
  jam_selesai: string;
  ruangan: string;
};

const days: ScheduleForm['hari'][] = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const router = useRouter();
const isSubmitting = ref(false);
const loadingSchedule = ref(false);
const formError = ref('');
const teachers = ref<TeacherOption[]>([]);
const subjects = ref<SubjectOption[]>([]);
const classrooms = ref<ClassroomOption[]>([]);
const academicYears = ref<AcademicYearOption[]>([]);
const teacherSubjects = ref<TeacherSubjectItem[]>([]);

const form = reactive<ScheduleForm>({
  tahun_ajaran_id: null,
  kelas_id: null,
  teacher_id: null,
  subject_id: null,
  hari: 'Senin',
  jam_mulai: '',
  jam_selesai: '',
  ruangan: '',
});

const fetchOptions = async () => {
  const [teachersResponse, subjectsResponse, classroomsResponse, academicYearsResponse, teacherSubjectsResponse] = await Promise.all([
    useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } }),
    useApi<{ data: SubjectOption[] }>('/subjects', { method: 'GET', query: { simple: true } }),
    useApi<{ data: ClassroomOption[] }>('/classes', { method: 'GET', query: { simple: true } }),
    useApi<{ data: AcademicYearOption[] }>('/academic-years', { method: 'GET', query: { simple: true } }),
    useApi<PaginatedResponse<TeacherSubjectItem>>('/teacher-subjects', { method: 'GET', query: { per_page: 200 } }),
  ]);

  teachers.value = teachersResponse.data;
  subjects.value = subjectsResponse.data;
  classrooms.value = classroomsResponse.data;
  academicYears.value = academicYearsResponse.data;
  teacherSubjects.value = teacherSubjectsResponse.data;

  if (props.mode === 'create' && !form.tahun_ajaran_id) {
    const activeYear = academicYears.value.find((item) => item.status_aktif);
    form.tahun_ajaran_id = activeYear?.id || academicYears.value[0]?.id || null;
  }
};

const loadSchedule = async () => {
  if (props.mode !== 'edit' || !props.scheduleId) {
    return;
  }

  loadingSchedule.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: ScheduleItem }>(`/schedules/${props.scheduleId}`, { method: 'GET' });
    const schedule = response.data;
    form.tahun_ajaran_id = schedule.tahun_ajaran_id;
    form.kelas_id = schedule.kelas_id;
    form.teacher_id = schedule.teacher_id;
    form.subject_id = schedule.subject_id;
    form.hari = schedule.hari;
    form.jam_mulai = schedule.jam_mulai.slice(0, 5);
    form.jam_selesai = schedule.jam_selesai.slice(0, 5);
    form.ruangan = schedule.ruangan || '';
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data jadwal.';
  } finally {
    loadingSchedule.value = false;
  }
};

const filteredSubjects = computed(() => {
  if (!form.teacher_id) {
    return [];
  }

  const allowedSubjectIds = teacherSubjects.value
    .filter((item) => item.teacher_id === form.teacher_id)
    .map((item) => item.subject_id);

  return subjects.value.filter((subject) => allowedSubjectIds.includes(subject.id));
});

watch(() => form.teacher_id, () => {
  if (!filteredSubjects.value.some((subject) => subject.id === form.subject_id)) {
    form.subject_id = null;
  }
});

const submitSchedule = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      tahun_ajaran_id: form.tahun_ajaran_id,
      kelas_id: form.kelas_id,
      teacher_id: form.teacher_id,
      subject_id: form.subject_id,
      hari: form.hari,
      jam_mulai: form.jam_mulai,
      jam_selesai: form.jam_selesai,
      ruangan: form.ruangan.trim() || null,
    };

    if (props.mode === 'create') {
      await useApi('/schedules', { method: 'POST', body: payload });
    } else if (props.scheduleId) {
      await useApi(`/schedules/${props.scheduleId}`, { method: 'PUT', body: payload });
    }

    emit('saved');
    await router.push('/jadwal-pelajaran');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan jadwal pelajaran.';
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(async () => {
  await fetchOptions();
  await loadSchedule();
});
</script>
