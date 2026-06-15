<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Relasi Guru Mapel Baru' : 'Edit Relasi Guru Mapel' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Pilih guru dan mata pelajaran yang diampu.</p>
    </div>

    <div v-if="loadingTeacherSubject" class="p-6 text-sm text-slate-500">Memuat data relasi...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitTeacherSubject">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Guru Mapel</h3>
          <p class="mt-1 text-sm text-slate-500">Satu guru bisa dihubungkan ke lebih dari satu mata pelajaran.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
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
            <select v-model="form.subject_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option :value="null">Pilih mata pelajaran</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                {{ subject.nama_mapel }} · {{ subject.kode_mapel }}
              </option>
            </select>
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/guru-mapel" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Relasi' : 'Update Relasi' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { SubjectOption, TeacherOption, TeacherSubjectItem } from '~/types/teacher-subject';

const props = defineProps<{
  mode: 'create' | 'edit';
  teacherSubjectId?: number | null;
}>();

const emit = defineEmits<{ saved: [] }>();

type TeacherSubjectForm = {
  teacher_id: number | null;
  subject_id: number | null;
};

const router = useRouter();
const toast = useToast();
const isSubmitting = ref(false);
const loadingTeacherSubject = ref(false);
const formError = ref('');
const teachers = ref<TeacherOption[]>([]);
const subjects = ref<SubjectOption[]>([]);

const form = reactive<TeacherSubjectForm>({
  teacher_id: null,
  subject_id: null,
});

const fetchOptions = async () => {
  const [teachersResponse, subjectsResponse] = await Promise.all([
    useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } }),
    useApi<{ data: SubjectOption[] }>('/subjects', { method: 'GET', query: { simple: true } }),
  ]);

  teachers.value = teachersResponse.data;
  subjects.value = subjectsResponse.data;
};

const loadTeacherSubject = async () => {
  if (props.mode !== 'edit' || !props.teacherSubjectId) {
    return;
  }

  loadingTeacherSubject.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: TeacherSubjectItem }>(`/teacher-subjects/${props.teacherSubjectId}`, { method: 'GET' });
    const teacherSubject = response.data;
    form.teacher_id = teacherSubject.teacher_id;
    form.subject_id = teacherSubject.subject_id;
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data guru mapel.';
  } finally {
    loadingTeacherSubject.value = false;
  }
};

const submitTeacherSubject = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      teacher_id: form.teacher_id,
      subject_id: form.subject_id,
    };

    if (props.mode === 'create') {
      await useApi('/teacher-subjects', { method: 'POST', body: payload });
    } else if (props.teacherSubjectId) {
      await useApi(`/teacher-subjects/${props.teacherSubjectId}`, { method: 'PUT', body: payload });
    }

    toast.success(props.mode === 'create' ? 'Relasi guru mapel berhasil ditambahkan.' : 'Relasi guru mapel berhasil diperbarui.');
    emit('saved');
    await router.push('/guru-mapel');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan relasi guru mapel.';
    toast.error(formError.value);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(async () => {
  await fetchOptions();
  await loadTeacherSubject();
});
</script>
