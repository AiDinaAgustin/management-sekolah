<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="border-b border-slate-300 px-6 py-5">
      <h2 class="text-lg font-semibold text-slate-900">{{ mode === 'create' ? 'Data Tahun Ajaran Baru' : 'Edit Tahun Ajaran' }}</h2>
      <p class="mt-1 text-sm text-slate-500">Lengkapi data tahun ajaran, lalu simpan perubahan.</p>
    </div>

    <div v-if="loadingAcademicYear" class="p-6 text-sm text-slate-500">Memuat data tahun ajaran...</div>

    <form v-else class="space-y-6 p-6" @submit.prevent="submitAcademicYear">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div class="rounded-[1.75rem] border border-slate-300 bg-slate-50/60 p-5">
        <div class="mb-5">
          <h3 class="text-base font-semibold text-slate-900">Data Tahun Ajaran</h3>
          <p class="mt-1 text-sm text-slate-500">Atur periode ajaran dan status aktif yang dipakai oleh kelas.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Nama Tahun Ajaran</span>
            <input
              v-model="form.nama_tahun_ajaran"
              type="text"
              class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300"
              placeholder="Contoh: 2026/2027"
            />
          </label>

          <label class="space-y-2 text-sm font-medium text-slate-700">
            <span>Semester</span>
            <select v-model="form.semester" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="Ganjil">Ganjil</option>
              <option value="Genap">Genap</option>
            </select>
          </label>

          <label class="md:col-span-2 flex items-center justify-between gap-4 rounded-2xl border border-slate-300 bg-white px-4 py-4">
            <div>
              <p class="text-sm font-semibold text-slate-800">Status Aktif</p>
              <p class="mt-1 text-sm text-slate-500">Jika aktif, tahun ajaran lain akan otomatis nonaktif.</p>
            </div>
            <button
              type="button"
              class="relative inline-flex h-8 w-14 items-center rounded-full transition"
              :class="form.status_aktif ? 'bg-indigo-600' : 'bg-slate-300'"
              @click="form.status_aktif = !form.status_aktif"
            >
              <span
                class="inline-block h-6 w-6 rounded-full bg-white shadow transition"
                :class="form.status_aktif ? 'translate-x-7' : 'translate-x-1'"
              />
            </button>
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <NuxtLink to="/tahun-ajaran" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
          Batal
        </NuxtLink>
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Tahun Ajaran' : 'Update Tahun Ajaran' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import type { AcademicYearItem } from '~/types/academic-year';

const props = defineProps<{
  mode: 'create' | 'edit';
  academicYearId?: number | null;
}>();

const emit = defineEmits<{ saved: [] }>();

type AcademicYearForm = {
  nama_tahun_ajaran: string;
  semester: 'Ganjil' | 'Genap';
  status_aktif: boolean;
};

const router = useRouter();
const toast = useToast();
const isSubmitting = ref(false);
const loadingAcademicYear = ref(false);
const formError = ref('');

const form = reactive<AcademicYearForm>({
  nama_tahun_ajaran: '',
  semester: 'Ganjil',
  status_aktif: true,
});

const loadAcademicYear = async () => {
  if (props.mode !== 'edit' || !props.academicYearId) {
    return;
  }

  loadingAcademicYear.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: AcademicYearItem }>(`/academic-years/${props.academicYearId}`, { method: 'GET' });
    const academicYear = response.data;
    form.nama_tahun_ajaran = academicYear.nama_tahun_ajaran;
    form.semester = academicYear.semester;
    form.status_aktif = academicYear.status_aktif;
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data tahun ajaran.';
  } finally {
    loadingAcademicYear.value = false;
  }
};

const submitAcademicYear = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      nama_tahun_ajaran: form.nama_tahun_ajaran.trim(),
      semester: form.semester,
      status_aktif: form.status_aktif,
    };

    if (props.mode === 'create') {
      await useApi('/academic-years', { method: 'POST', body: payload });
    } else if (props.academicYearId) {
      await useApi(`/academic-years/${props.academicYearId}`, { method: 'PUT', body: payload });
    }

    toast.success(props.mode === 'create' ? 'Tahun ajaran berhasil ditambahkan.' : 'Tahun ajaran berhasil diperbarui.');
    emit('saved');
    await router.push('/tahun-ajaran');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan data tahun ajaran.';
    toast.error(formError.value);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(loadAcademicYear);
</script>
