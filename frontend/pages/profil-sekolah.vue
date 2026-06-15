<template>
  <div class="mx-auto max-w-4xl space-y-6">
    <div class="rounded-[2rem] border border-slate-300 bg-white p-6 shadow-sm sm:p-8">
      <div class="flex flex-col gap-3 border-b border-slate-300 pb-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">Profil Sekolah</h3>
          <p class="mt-1 text-sm text-slate-500">Atur nama sekolah dan tagline yang tampil di sidebar aplikasi.</p>
        </div>
      </div>

      <form class="mt-6 space-y-6" @submit.prevent="saveProfile">
        <label class="block space-y-2">
          <span class="text-sm font-semibold text-slate-700">Nama Sekolah</span>
          <input
            v-model="form.nama_sekolah"
            type="text"
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300"
            placeholder="Masukkan nama sekolah"
          />
        </label>

        <label class="block space-y-2">
          <span class="text-sm font-semibold text-slate-700">Tagline</span>
          <input
            v-model="form.tagline"
            type="text"
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300"
            placeholder="Masukkan tagline sekolah"
          />
        </label>

        <div v-if="message.text" :class="message.type === 'error' ? 'border-red-200 bg-red-50 text-red-600' : 'border-emerald-200 bg-emerald-50 text-emerald-600'" class="rounded-2xl border px-4 py-3 text-sm">
          {{ message.text }}
        </div>

        <div class="flex items-center justify-end">
          <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-70"
            :disabled="isSubmitting || isLoading"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M17 3a2 2 0 0 1 2 2v14l-7-3-7 3V5a2 2 0 0 1 2-2h10Zm0 2H7v10.764l5-2.143 5 2.143V5Z" />
            </svg>
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Pengaturan',
  pageTitle: 'Profil Sekolah',
  pageDescription: 'Kelola nama sekolah dan informasi singkat yang tampil di aplikasi.',
});

const auth = useAuthStore();
const router = useRouter();
const toast = useToast();
const schoolProfile = useSchoolProfile();
const isLoading = ref(true);
const isSubmitting = ref(false);
const form = reactive({
  nama_sekolah: '',
  tagline: '',
});
const message = reactive({
  type: 'success' as 'success' | 'error',
  text: '',
});

const loadProfile = async () => {
  isLoading.value = true;
  message.text = '';

  try {
    const response = await useApi<{ data: { nama_sekolah: string; tagline: string | null } }>('/school-profile', {
      method: 'GET',
    });

    form.nama_sekolah = response.data.nama_sekolah || '';
    form.tagline = response.data.tagline || '';
    schoolProfile.value.namaSekolah = form.nama_sekolah || 'School Admin';
    schoolProfile.value.tagline = form.tagline || 'Sistem Informasi Sekolah';
  } catch (error: any) {
    message.type = 'error';
    message.text = error?.data?.message || 'Gagal memuat profil sekolah.';
  } finally {
    isLoading.value = false;
  }
};

const saveProfile = async () => {
  if (auth.user?.role !== 'admin') {
    await router.push('/');
    return;
  }

  isSubmitting.value = true;
  message.text = '';

  try {
    await useApi('/school-profile', {
      method: 'PUT',
      body: {
        nama_sekolah: form.nama_sekolah,
        tagline: form.tagline || null,
      },
    });

    schoolProfile.value.namaSekolah = form.nama_sekolah || 'School Admin';
    schoolProfile.value.tagline = form.tagline || 'Sistem Informasi Sekolah';
    message.type = 'success';
    message.text = 'Profil sekolah berhasil diperbarui.';
    toast.success(message.text);
  } catch (error: any) {
    message.type = 'error';
    message.text = error?.data?.message || 'Gagal menyimpan profil sekolah.';
    toast.error(message.text);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(async () => {
  if (auth.user?.role !== 'admin') {
    await router.push('/');
    return;
  }

  await loadProfile();
});
</script>
