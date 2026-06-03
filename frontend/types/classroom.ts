export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
  email?: string | null;
};

export type AcademicYearOption = {
  id: number;
  nama_tahun_ajaran: string;
  semester: string;
  status_aktif: boolean;
};

export type ClassroomItem = {
  id: number;
  tingkat: number;
  rombel: string;
  nama_kelas: string;
  wali_kelas_id: number | null;
  tahun_ajaran_id: number;
  jumlah_siswa?: number | null;
  wali_kelas?: TeacherOption | null;
  tahun_ajaran?: AcademicYearOption | null;
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
