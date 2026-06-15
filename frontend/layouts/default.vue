<template>
  <div class="h-screen overflow-hidden p-3 lg:p-6">
    <div class="flex h-[calc(100vh-1.5rem)] overflow-hidden rounded-[2rem] border border-white/60 bg-white/70 shadow-[0_20px_60px_rgba(76,81,191,0.15)] backdrop-blur-xl lg:h-[calc(100vh-3rem)]">
      <aside class="hidden h-full w-72 shrink-0 overflow-hidden bg-gradient-to-b from-indigo-600 via-indigo-700 to-blue-900 text-white lg:flex lg:flex-col">
        <div class="shrink-0 border-b border-white/10 p-6">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-white/10">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 3 2 8l10 5 8-4v6h2V8L12 3Zm-6 9v4.5C6 19 8.686 21 12 21s6-2 6-4.5V12l-6 3-6-3Z" />
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold">{{ schoolProfile.namaSekolah }}</h1>
              <p class="text-sm text-indigo-100/80">{{ schoolProfile.tagline }}</p>
            </div>
          </div>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto p-4">
          <nav class="space-y-2">
            <template v-for="group in menu">
              <!-- Standalone item (tanpa grup), mis. Dashboard -->
              <NuxtLink
                v-if="!group.items"
                :key="`link-${group.key}`"
                :to="group.to"
                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-indigo-100/80 transition hover:bg-white/10 hover:text-white"
                :class="isActiveMenu(group.to) ? 'bg-white text-indigo-700 shadow-lg shadow-indigo-950/20 hover:bg-white hover:text-indigo-700' : ''"
              >
                <span class="flex h-8 w-8 items-center justify-center rounded-xl" :class="isActiveMenu(group.to) ? 'bg-indigo-50 text-indigo-600' : 'bg-white/10 text-indigo-100'">
                  <component :is="group.icon" class="h-4 w-4" />
                </span>
                {{ group.label }}
              </NuxtLink>

              <!-- Grup dengan sub-menu (accordion) -->
              <div v-else :key="`group-${group.key}`">
                <button
                  type="button"
                  class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-indigo-100/80 transition hover:bg-white/10 hover:text-white"
                  @click="toggleGroup(group.key)"
                >
                  <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-indigo-100">
                    <component :is="group.icon" class="h-4 w-4" />
                  </span>
                  <span class="flex-1 text-left">{{ group.label }}</span>
                  <svg
                    class="h-4 w-4 shrink-0 transition-transform duration-200"
                    :class="isGroupOpen(group.key) ? 'rotate-180' : ''"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06Z" clip-rule="evenodd" />
                  </svg>
                </button>

                <div v-show="isGroupOpen(group.key)" class="mt-1 space-y-1 pl-4">
                  <NuxtLink
                    v-for="item in group.items"
                    :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-medium text-indigo-100/70 transition hover:bg-white/10 hover:text-white"
                    :class="isActiveMenu(item.to) ? 'bg-white text-indigo-700 shadow-lg shadow-indigo-950/20 hover:bg-white hover:text-indigo-700' : ''"
                  >
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg" :class="isActiveMenu(item.to) ? 'bg-indigo-50 text-indigo-600' : 'bg-white/10 text-indigo-100'">
                      <component :is="item.icon" class="h-3.5 w-3.5" />
                    </span>
                    {{ item.label }}
                  </NuxtLink>
                </div>
              </div>
            </template>
          </nav>
        </div>
        <div class="shrink-0 p-4">
          <div class="rounded-3xl border border-white/10 bg-white/10 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-indigo-100/70">School Panel</p>
            <p class="mt-2 text-sm text-indigo-50">Kelola data sekolah, absensi, nilai, dan rapot dalam satu dashboard.</p>
          </div>
        </div>
      </aside>

      <main class="flex flex-1 flex-col overflow-hidden bg-white/30">
        <header class="shrink-0 border-b border-slate-200/70 bg-white/70 px-4 py-5 backdrop-blur-md lg:px-8">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-indigo-500">{{ pageEyebrow }}</p>
              <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ pageTitle }}</h2>
              <p class="text-sm text-slate-500">{{ pageDescription }}</p>
            </div>
            <div ref="userMenuRef" class="relative">
              <button
                type="button"
                class="flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm shadow-indigo-100 transition hover:bg-white"
                @click="toggleUserMenu"
              >
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-blue-500 text-white shadow-md shadow-indigo-200">
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8ZM3 16a5 5 0 015-5h4a5 5 0 015 5v1H3v-1Z" clip-rule="evenodd" />
                  </svg>
                </span>
                <span class="text-left">
                  <span class="block text-xs text-slate-400">Signed in as</span>
                  <span>{{ auth.user?.name || 'Admin' }}</span>
                </span>
                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06Z" clip-rule="evenodd" />
                </svg>
              </button>

              <div
                v-if="isUserMenuOpen"
                class="absolute right-0 z-20 mt-2 w-48 rounded-2xl border border-white/70 bg-white/95 p-2 shadow-[0_18px_40px_rgba(15,23,42,0.18)] backdrop-blur"
              >
                <button
                  type="button"
                  class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="isLoggingOut"
                  @click="logout"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 4.75A1.75 1.75 0 014.75 3h5.5a.75.75 0 010 1.5h-5.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h5.5a.75.75 0 010 1.5h-5.5A1.75 1.75 0 013 15.25V4.75Zm9.78 1.97a.75.75 0 011.06 0l2.75 2.75a.75.75 0 010 1.06l-2.75 2.75a.75.75 0 11-1.06-1.06l1.47-1.47H8.75a.75.75 0 010-1.5h5.5l-1.47-1.47a.75.75 0 010-1.06Z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ isLoggingOut ? 'Logout...' : 'Logout' }}</span>
                </button>
              </div>
            </div>
          </div>
        </header>

        <section class="flex-1 overflow-y-auto p-4 lg:p-8">
          <slot />
        </section>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const isLoggingOut = ref(false);
const isUserMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);
const currentRoute = computed(() => route.meta ?? {});
const schoolProfile = useSchoolProfile();

type MenuLeaf = { label: string; to: string; icon: ReturnType<typeof resolveComponent> };
type MenuEntry = MenuLeaf & { key: string; items?: MenuLeaf[] };

const allMenu: MenuEntry[] = [
  { key: 'dashboard', label: 'Dashboard', to: '/', icon: resolveComponent('IconDashboard') },
  {
    key: 'data-master',
    label: 'Data Master',
    to: '',
    icon: resolveComponent('IconUsers'),
    items: [
      { label: 'Siswa', to: '/siswa', icon: resolveComponent('IconUsers') },
      { label: 'Guru', to: '/guru', icon: resolveComponent('IconTeacher') },
      { label: 'Guru Mapel', to: '/guru-mapel', icon: resolveComponent('IconBook') },
      { label: 'Kelas', to: '/kelas', icon: resolveComponent('IconClassroom') },
      { label: 'Tahun Ajaran', to: '/tahun-ajaran', icon: resolveComponent('IconCalendar') },
      { label: 'Mata Pelajaran', to: '/mata-pelajaran', icon: resolveComponent('IconBook') },
    ],
  },
  {
    key: 'akademik',
    label: 'Akademik',
    to: '',
    icon: resolveComponent('IconCalendar'),
    items: [
      { label: 'Jadwal Pelajaran', to: '/jadwal-pelajaran', icon: resolveComponent('IconCalendar') },
      { label: 'Nilai', to: '/nilai', icon: resolveComponent('IconChart') },
      { label: 'Rapot', to: '/rapot', icon: resolveComponent('IconDocument') },
    ],
  },
  {
    key: 'absensi',
    label: 'Absensi',
    to: '',
    icon: resolveComponent('IconCalendar'),
    items: [
      { label: 'Absensi', to: '/absensi', icon: resolveComponent('IconCalendar') },
      { label: 'Absensi Mapel', to: '/absensi-pelajaran', icon: resolveComponent('IconCalendar') },
    ],
  },
  {
    key: 'pengaturan',
    label: 'Pengaturan',
    to: '',
    icon: resolveComponent('IconHome'),
    items: [
      { label: 'Profil Sekolah', to: '/profil-sekolah', icon: resolveComponent('IconHome') },
    ],
  },
];

const guruAllowedMenu = ['/', '/kelas', '/jadwal-pelajaran', '/absensi', '/absensi-pelajaran', '/nilai'];
const menu = computed<MenuEntry[]>(() => {
  if (auth.user?.role !== 'guru') {
    return allMenu;
  }

  return allMenu
    .map((group) => {
      if (!group.items) {
        return guruAllowedMenu.includes(group.to) ? group : null;
      }

      const items = group.items.filter((item) => guruAllowedMenu.includes(item.to));

      return items.length ? { ...group, items } : null;
    })
    .filter((group): group is MenuEntry => group !== null);
});

const openGroups = ref<Record<string, boolean>>({});
const isGroupOpen = (key: string) => openGroups.value[key] ?? false;
const toggleGroup = (key: string) => {
  openGroups.value[key] = !isGroupOpen(key);
};

const isActiveMenu = (path: string) => {
  if (path === '/') {
    return route.path === '/';
  }

  return route.path === path || route.path.startsWith(`${path}/`);
};

const pageEyebrow = computed(() => String(currentRoute.value.pageEyebrow || 'Admin Portal'));
const pageTitle = computed(() => String(currentRoute.value.pageTitle || 'Dashboard'));
const pageDescription = computed(() => String(currentRoute.value.pageDescription || 'Pantau aktivitas sekolah dengan tampilan yang lebih modern.'));

const fetchSchoolProfile = async () => {
  try {
    const response = await useApi<{ data: { nama_sekolah: string; tagline: string | null } }>('/school-profile', {
      method: 'GET',
    });

    schoolProfile.value.namaSekolah = response.data.nama_sekolah || 'School Admin';
    schoolProfile.value.tagline = response.data.tagline || 'Sistem Informasi Sekolah';
  } catch {}
};

const logout = async () => {
  if (isLoggingOut.value) {
    return;
  }

  isLoggingOut.value = true;
  isUserMenuOpen.value = false;

  try {
    if (auth.token) {
      await useApi('/auth/logout', {
        method: 'POST',
      });
    }
  } catch {
  } finally {
    auth.clearAuth();
    isLoggingOut.value = false;
    await router.push('/login');
  }
};

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const handleClickOutside = (event: MouseEvent) => {
  if (!userMenuRef.value) {
    return;
  }

  const target = event.target as Node | null;

  if (target && !userMenuRef.value.contains(target)) {
    isUserMenuOpen.value = false;
  }
};

onMounted(() => {
  fetchSchoolProfile();
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
