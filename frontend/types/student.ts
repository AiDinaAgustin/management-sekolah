export type StudentItem = {
  id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  tempat_lahir: string | null;
  tanggal_lahir: string | null;
  alamat: string | null;
  kelas_id: number | null;
  orang_tua_id: number | null;
  status_aktif: boolean;
  kelas?: {
    id: number;
    nama_kelas: string;
  } | null;
  orang_tua?: {
    id: number;
    nama_ayah: string;
    no_hp_ayah?: string | null;
    pekerjaan_ayah?: string | null;
    nama_ibu: string;
    no_hp_ibu?: string | null;
    pekerjaan_ibu?: string | null;
    alamat?: string | null;
  } | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type ClassroomOption = {
  id: number;
  nama_kelas: string;
  tingkat?: number;
  rombel?: string;
};

export type PaginatedResponse<T> = {
  data: T[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
  };
};
