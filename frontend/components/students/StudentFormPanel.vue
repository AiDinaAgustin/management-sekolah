<template>
  <div class="rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <form class="space-y-5" @submit.prevent="submitStudent">
      <div v-if="formError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        {{ formError }}
      </div>

      <div v-if="loadingStudent" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
        Memuat data siswa...
      </div>

      <template v-else>
        <div class="space-y-6">
          <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50/40 p-5">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900">Data Siswa</h3>
              <p class="text-sm text-slate-500">Lengkapi identitas utama siswa dan informasi akademiknya.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">NIS</span>
                <input v-model="form.nis" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Nama lengkap</span>
                <input v-model="form.nama_lengkap" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Jenis kelamin</span>
                <select v-model="form.jenis_kelamin" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white">
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Status</span>
                <select v-model="form.status_aktif" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white">
                  <option :value="true">Aktif</option>
                  <option :value="false">Nonaktif</option>
                </select>
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Tempat lahir</span>
                <input v-model="form.tempat_lahir" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Tanggal lahir</span>
                <input v-model="form.tanggal_lahir" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Kelas</span>
                <select v-model="form.kelas_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white">
                  <option :value="null">Pilih kelas</option>
                  <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                    {{ classroom.nama_kelas }}
                  </option>
                </select>
              </label>
            </div>

            <label class="mt-4 block space-y-2">
              <span class="text-sm font-medium text-slate-700">Alamat siswa</span>
              <textarea v-model="form.alamat" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white"></textarea>
            </label>
          </section>

          <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50/40 p-5">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900">Data Ayah</h3>
              <p class="text-sm text-slate-500">Masukkan identitas dan kontak ayah siswa.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Nama ayah</span>
                <input v-model="form.nama_ayah" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">No. HP ayah</span>
                <input v-model="form.no_hp_ayah" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Pekerjaan ayah</span>
                <input v-model="form.pekerjaan_ayah" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
            </div>
          </section>

          <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50/40 p-5">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-slate-900">Data Ibu</h3>
              <p class="text-sm text-slate-500">Masukkan identitas dan kontak ibu siswa.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Nama ibu</span>
                <input v-model="form.nama_ibu" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">No. HP ibu</span>
                <input v-model="form.no_hp_ibu" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Pekerjaan ibu</span>
                <input v-model="form.pekerjaan_ibu" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white" />
              </label>
            </div>

            <label class="mt-4 block space-y-2">
              <span class="text-sm font-medium text-slate-700">Alamat orang tua</span>
              <textarea v-model="form.alamat_orang_tua" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white"></textarea>
            </label>
          </section>
        </div>

        <div class="flex justify-end gap-3">
          <NuxtLink to="/siswa" class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600">
            Batal
          </NuxtLink>
          <button type="submit" class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:opacity-60" :disabled="isSubmitting">
            {{ isSubmitting ? 'Menyimpan...' : mode === 'create' ? 'Simpan Siswa' : 'Update Siswa' }}
          </button>
        </div>
      </template>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { ClassroomOption, StudentItem } from '~/types/student';

const props = defineProps<{
  mode: 'create' | 'edit';
  studentId?: number;
}>();

const emit = defineEmits<{
  saved: [];
}>();

type StudentForm = {
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  tempat_lahir: string;
  tanggal_lahir: string;
  alamat: string;
  kelas_id: number | null;
  status_aktif: boolean;
  nama_ayah: string;
  no_hp_ayah: string;
  pekerjaan_ayah: string;
  nama_ibu: string;
  no_hp_ibu: string;
  pekerjaan_ibu: string;
  alamat_orang_tua: string;
};

const router = useRouter();
const isSubmitting = ref(false);
const loadingStudent = ref(false);
const formError = ref('');
const classrooms = ref<ClassroomOption[]>([]);

const createInitialForm = (): StudentForm => ({
  nis: '',
  nama_lengkap: '',
  jenis_kelamin: 'L',
  tempat_lahir: '',
  tanggal_lahir: '',
  alamat: '',
  kelas_id: null,
  status_aktif: true,
  nama_ayah: '',
  no_hp_ayah: '',
  pekerjaan_ayah: '',
  nama_ibu: '',
  no_hp_ibu: '',
  pekerjaan_ibu: '',
  alamat_orang_tua: '',
});

const form = reactive<StudentForm>(createInitialForm());

const normalizeDateForInput = (value: string | null | undefined) => {
  if (!value) {
    return '';
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return '';
  }

  const year = parsedDate.getFullYear();
  const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
  const day = String(parsedDate.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const fetchOptions = async () => {
  const classroomResponse = await useApi<{ data: ClassroomOption[] }>('/classes', { method: 'GET', query: { simple: true } });

  classrooms.value = classroomResponse.data;
};

const loadStudent = async () => {
  if (props.mode !== 'edit' || !props.studentId) {
    return;
  }

  loadingStudent.value = true;
  formError.value = '';

  try {
    const response = await useApi<{ data: StudentItem }>(`/students/${props.studentId}`, { method: 'GET' });
    const student = response.data;
    form.nis = student.nis;
    form.nama_lengkap = student.nama_lengkap;
    form.jenis_kelamin = student.jenis_kelamin;
    form.tempat_lahir = student.tempat_lahir || '';
    form.tanggal_lahir = normalizeDateForInput(student.tanggal_lahir);
    form.alamat = student.alamat || '';
    form.kelas_id = student.kelas_id;
    form.status_aktif = student.status_aktif;
    form.nama_ayah = student.orang_tua?.nama_ayah || '';
    form.no_hp_ayah = student.orang_tua?.no_hp_ayah || '';
    form.pekerjaan_ayah = student.orang_tua?.pekerjaan_ayah || '';
    form.nama_ibu = student.orang_tua?.nama_ibu || '';
    form.no_hp_ibu = student.orang_tua?.no_hp_ibu || '';
    form.pekerjaan_ibu = student.orang_tua?.pekerjaan_ibu || '';
    form.alamat_orang_tua = student.orang_tua?.alamat || '';
  } catch (error: any) {
    formError.value = error?.data?.message || 'Gagal memuat data siswa.';
  } finally {
    loadingStudent.value = false;
  }
};

const submitStudent = async () => {
  isSubmitting.value = true;
  formError.value = '';

  try {
    const payload = {
      ...form,
      tempat_lahir: form.tempat_lahir || null,
      tanggal_lahir: form.tanggal_lahir || null,
      alamat: form.alamat || null,
      kelas_id: form.kelas_id || null,
      no_hp_ayah: form.no_hp_ayah || null,
      pekerjaan_ayah: form.pekerjaan_ayah || null,
      no_hp_ibu: form.no_hp_ibu || null,
      pekerjaan_ibu: form.pekerjaan_ibu || null,
      alamat_orang_tua: form.alamat_orang_tua || null,
    };

    if (props.mode === 'create') {
      await useApi('/students', { method: 'POST', body: payload });
    } else if (props.studentId) {
      await useApi(`/students/${props.studentId}`, { method: 'PUT', body: payload });
    }

    emit('saved');
    await router.push('/siswa');
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    formError.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan data siswa.';
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(async () => {
  await fetchOptions();
  await loadStudent();
});
</script>
