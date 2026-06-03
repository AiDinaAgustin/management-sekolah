export const useApi = async <T>(path: string, options: Parameters<typeof $fetch<T>>[1] = {}) => {
  const config = useRuntimeConfig();
  const auth = useAuthStore();
  const headers = new Headers(options.headers || {});

  if (auth.token) {
    headers.set('Authorization', `Bearer ${auth.token}`);
  }

  headers.set('Accept', 'application/json');

  return await $fetch<T>(`${config.public.apiBase}${path}`, {
    ...options,
    headers,
  });
};
