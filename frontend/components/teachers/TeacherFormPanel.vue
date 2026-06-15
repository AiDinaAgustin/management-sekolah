<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Data Guru Baru' : 'Edit Data Guru' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Lengkapi data guru, lalu simpan perubahan.</p>
    </div>

    <div v-if="loadingTeacher" class="p-6 text-sm text-slate-500">Memuat data guru...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitTeacher">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Guru</h3>
          <p class="mt-1 text-sm text-slate-500">Atur identitas dasar guru yang dipakai di seluruh sistem.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>NIP</span>
            <input v-model="form.nip" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Masukkan NIP" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Nama Guru</span>
            <input v-model="form.nama" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Masukkan nama guru" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Email</span>
            <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Masukkan email guru" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>No. HP</span>
            <input v-model="form.no_hp" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Masukkan nomor HP" />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700 md:col-span-2">
            <span>Alamat</span>
            <textarea v-model="form.alamat" rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Masukkan alamat guru" />
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/guru" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Guru' : 'Update Guru' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { TeacherItem } from '~/types/teacher';

const props = defineProps<{
  mode: 'create' | 'edit';
  teacherId?: number | null;
}>();

const emit = defineEmits<{ saved: [] }>();

type TeacherForm = {
  nip: string;
  nama: string;
  email: string;
  no_hp: string;
  alamat: string;
};

const router = useRouter();
const toast = useToast();
const isSubmitting = ref(false);
const loadingTeacher = ref(false);
const formError = ref('');

const form = reactive<TeacherForm>({
  nip: '',
  nama: '',
  email: '',
  no_hp: '',
  alamat: '',
});

const loadTeacher = async () => {
  if (props.mode !== 'edit' || !props.teacherId) {
    return;
  }

  loadingTeacher.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: TeacherItem }>(`/teachers/${props.teacherId}`, { method: 'GET' });
    const teacher = response.data;
    form.nip = teacher.nip;
    form.nama = teacher.nama;
    form.email = teacher.email;
    form.no_hp = teacher.no_hp || '';
    form.alamat = teacher.alamat || '';
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data guru.';
  } finally {
    loadingTeacher.value = false;
  }
};

const submitTeacher = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      nip: form.nip.trim(),
      nama: form.nama.trim(),
      email: form.email.trim(),
      no_hp: form.no_hp.trim() || null,
      alamat: form.alamat.trim() || null,
    };

    if (props.mode === 'create') {
      await useApi('/teachers', { method: 'POST', body: payload });
    } else if (props.teacherId) {
      await useApi(`/teachers/${props.teacherId}`, { method: 'PUT', body: payload });
    }

    toast.success(props.mode === 'create' ? 'Data guru berhasil ditambahkan.' : 'Data guru berhasil diperbarui.');
    emit('saved');
    await router.push('/guru');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan data guru.';
    toast.error(formError.value);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(loadTeacher);
</script>
