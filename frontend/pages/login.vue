<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-100 p-4">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl">
      <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-slate-900">Login Sekolah</h1>
        <p class="mt-2 text-sm text-slate-500">Masuk ke dashboard sistem informasi sekolah</p>
      </div>
      <form class="space-y-4" @submit.prevent="submitLogin">
        <input v-model="form.email" class="w-full rounded-xl border border-slate-200 px-4 py-3" type="email" placeholder="Email" />
        <input v-model="form.password" class="w-full rounded-xl border border-slate-200 px-4 py-3" type="password" placeholder="Password" />
        <p v-if="errorMessage" class="text-sm text-rose-500">{{ errorMessage }}</p>
        <button :disabled="isSubmitting" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-medium text-white disabled:opacity-60">
          {{ isSubmitting ? 'Memproses...' : 'Login' }}
        </button>
      </form>
      <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">
        Demo: `admin@sekolah.test` / `password`
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ['guest'],
});

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
  email: 'admin@sekolah.test',
  password: 'password',
});

const isSubmitting = ref(false);
const errorMessage = ref('');

const submitLogin = async () => {
  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    const response = await useApi<{ token: string; user: import('~/stores/auth').AuthUser }>('/auth/login', {
      method: 'POST',
      body: form,
    });

    auth.setAuth(response);
    await router.push('/');
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Login gagal. Cek kembali email dan password.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
