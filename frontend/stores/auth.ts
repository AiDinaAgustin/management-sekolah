import { defineStore } from 'pinia';

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'guru';
  teacher?: {
    id: number;
    nama: string;
    nip: string;
  } | null;
};

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: '' as string,
    user: null as AuthUser | null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    syncCookie(payload: { token: string; user: AuthUser } | null) {
      const authCookie = useCookie<{ token: string; user: AuthUser } | null>('school-auth', {
        sameSite: 'lax',
        watch: false,
      });

      authCookie.value = payload;
    },
    setAuth(payload: { token: string; user: AuthUser }) {
      this.token = payload.token;
      this.user = payload.user;
      this.syncCookie(payload);

      if (process.client) {
        localStorage.setItem('school-auth', JSON.stringify(payload));
      }
    },
    clearAuth() {
      this.token = '';
      this.user = null;
      this.syncCookie(null);

      if (process.client) {
        localStorage.removeItem('school-auth');
      }
    },
    hydrate() {
      const authCookie = useCookie<{ token: string; user: AuthUser } | null>('school-auth', {
        sameSite: 'lax',
        watch: false,
      });

      if (authCookie.value) {
        this.token = authCookie.value.token;
        this.user = authCookie.value.user;
        return;
      }

      if (!process.client) {
        return;
      }

      const savedAuth = localStorage.getItem('school-auth');

      if (!savedAuth) {
        return;
      }

      try {
        const parsedAuth = JSON.parse(savedAuth) as { token: string; user: AuthUser };
        this.token = parsedAuth.token;
        this.user = parsedAuth.user;
        this.syncCookie(parsedAuth);
      } catch {
        this.clearAuth();
      }
    },
  },
});
