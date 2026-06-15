<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Data Mata Pelajaran Baru' : 'Edit Mata Pelajaran' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Lengkapi data mata pelajaran, lalu simpan perubahan.</p>
    </div>

    <div v-if="loadingSubject" class="p-6 text-sm text-slate-500">Memuat data mata pelajaran...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitSubject">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Mata Pelajaran</h3>
          <p class="mt-1 text-sm text-slate-500">Atur kode dan nama mata pelajaran yang dipakai di sistem.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Kode Mapel</span>
            <input v-model="form.kode_mapel" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm uppercase outline-none transition focus:border-indigo-300" placeholder="Contoh: MTK" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Nama Mata Pelajaran</span>
            <input v-model="form.nama_mapel" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Contoh: Matematika" />
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/mata-pelajaran" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Mata Pelajaran' : 'Update Mata Pelajaran' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { SubjectItem } from '~/types/subject';

const props = defineProps<{
  mode: 'create' | 'edit';
  subjectId?: number | null;
}>();

const emit = defineEmits<{ saved: [] }>();

type SubjectForm = {
  kode_mapel: string;
  nama_mapel: string;
};

const router = useRouter();
const toast = useToast();
const isSubmitting = ref(false);
const loadingSubject = ref(false);
const formError = ref('');

const form = reactive<SubjectForm>({
  kode_mapel: '',
  nama_mapel: '',
});

const loadSubject = async () => {
  if (props.mode !== 'edit' || !props.subjectId) {
    return;
  }

  loadingSubject.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: SubjectItem }>(`/subjects/${props.subjectId}`, { method: 'GET' });
    const subject = response.data;
    form.kode_mapel = subject.kode_mapel;
    form.nama_mapel = subject.nama_mapel;
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data mata pelajaran.';
  } finally {
    loadingSubject.value = false;
  }
};

const submitSubject = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      kode_mapel: form.kode_mapel.trim().toUpperCase(),
      nama_mapel: form.nama_mapel.trim(),
    };

    if (props.mode === 'create') {
      await useApi('/subjects', { method: 'POST', body: payload });
    } else if (props.subjectId) {
      await useApi(`/subjects/${props.subjectId}`, { method: 'PUT', body: payload });
    }

    toast.success(props.mode === 'create' ? 'Mata pelajaran berhasil ditambahkan.' : 'Mata pelajaran berhasil diperbarui.');
    emit('saved');
    await router.push('/mata-pelajaran');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan data mata pelajaran.';
    toast.error(formError.value);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(loadSubject);
</script>
