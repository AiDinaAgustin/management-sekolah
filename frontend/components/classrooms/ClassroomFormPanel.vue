<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Data Kelas Baru' : 'Edit Data Kelas' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Lengkapi data kelas, lalu simpan perubahan.</p>
    </div>

    <div v-if="loadingClassroom" class="p-6 text-sm text-slate-500">Memuat data kelas...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitClassroom">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Kelas</h3>
          <p class="mt-1 text-sm text-slate-500">Atur identitas kelas, wali kelas, dan tahun ajaran aktif.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Tingkat</span>
            <input
              v-model.number="form.tingkat"
              type="number"
              min="1"
              max="12"
              class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300"
              placeholder="Contoh: 7"
            />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Rombel</span>
            <input
              v-model="form.rombel"
              type="text"
              maxlength="10"
              class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm uppercase outline-none transition focus:border-indigo-300"
              placeholder="Contoh: A"
            />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Nama Kelas</span>
            <input
              v-model="form.nama_kelas"
              type="text"
              readonly
              class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 outline-none transition"
              placeholder="Otomatis dari tingkat dan rombel"
            />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Wali Kelas</span>
            <select v-model="form.wali_kelas_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih wali kelas</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.nama }} · {{ teacher.nip }}
              </option>
            </select>
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700 md:col-span-2">
            <span>Tahun Ajaran</span>
            <select v-model="form.tahun_ajaran_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih tahun ajaran</option>
              <option v-for="academicYear in academicYears" :key="academicYear.id" :value="academicYear.id">
                {{ academicYear.nama_tahun_ajaran }} · {{ academicYear.semester }}{{ academicYear.status_aktif ? ' · Aktif' : '' }}
              </option>
            </select>
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/kelas" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Kelas' : 'Update Kelas' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { AcademicYearOption, ClassroomItem, TeacherOption } from '~/types/classroom';

const props = defineProps<{
  mode: 'create' | 'edit';
  classroomId?: number | null;
}>();

const emit = defineEmits<{
  saved: [];
}>();

type ClassroomForm = {
  tingkat: number | null;
  rombel: string;
  nama_kelas: string;
  wali_kelas_id: number | null;
  tahun_ajaran_id: number | null;
};

const router = useRouter();
const isSubmitting = ref(false);
const loadingClassroom = ref(false);
const formError = ref('');
const teachers = ref<TeacherOption[]>([]);
const academicYears = ref<AcademicYearOption[]>([]);

const createInitialForm = (): ClassroomForm => ({
  tingkat: null,
  rombel: '',
  nama_kelas: '',
  wali_kelas_id: null,
  tahun_ajaran_id: null,
});

const form = reactive<ClassroomForm>(createInitialForm());

const fetchOptions = async () => {
  const [teachersResponse, academicYearsResponse] = await Promise.all([
    useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET' }),
    useApi<{ data: AcademicYearOption[] }>('/academic-years', { method: 'GET' }),
  ]);

  teachers.value = teachersResponse.data;
  academicYears.value = academicYearsResponse.data;

  if (props.mode === 'create' && !form.tahun_ajaran_id) {
    const activeYear = academicYears.value.find((item) => item.status_aktif);
    form.tahun_ajaran_id = activeYear?.id || academicYears.value[0]?.id || null;
  }
};

const loadClassroom = async () => {
  if (props.mode !== 'edit' || !props.classroomId) {
    return;
  }

  loadingClassroom.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: ClassroomItem }>(`/classes/${props.classroomId}`, { method: 'GET' });
    const classroom = response.data;
    form.tingkat = classroom.tingkat;
    form.rombel = classroom.rombel;
    form.nama_kelas = classroom.nama_kelas;
    form.wali_kelas_id = classroom.wali_kelas_id;
    form.tahun_ajaran_id = classroom.tahun_ajaran_id;
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data kelas.';
  } finally {
    loadingClassroom.value = false;
  }
};

const submitClassroom = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      tingkat: form.tingkat,
      rombel: form.rombel.trim().toUpperCase(),
      nama_kelas: form.nama_kelas.trim(),
      wali_kelas_id: form.wali_kelas_id || null,
      tahun_ajaran_id: form.tahun_ajaran_id,
    };

    if (props.mode === 'create') {
      await useApi('/classes', { method: 'POST', body: payload });
    } else if (props.classroomId) {
      await useApi(`/classes/${props.classroomId}`, { method: 'PUT', body: payload });
    }

    emit('saved');
    await router.push('/kelas');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan data kelas.';
  } finally {
    isSubmitting.value = false;
  }
};

watch(
  () => [form.tingkat, form.rombel] as const,
  ([tingkat, rombel], [oldTingkat, oldRombel]) => {
    const previousGenerated = `${oldTingkat || ''}${oldRombel || ''}`.trim();
    const nextGenerated = `${tingkat || ''}${rombel || ''}`.trim().toUpperCase();

    if (!form.nama_kelas || form.nama_kelas === previousGenerated) {
      form.nama_kelas = nextGenerated;
    }
  },
);

onMounted(async () => {
  await fetchOptions();
  await loadClassroom();
});
</script>
