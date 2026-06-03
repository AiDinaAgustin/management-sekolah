export type ScheduleItem = {
  id: number;
  tahun_ajaran_id: number;
  kelas_id: number;
  teacher_id: number;
  subject_id: number;
  hari: 'Senin' | 'Selasa' | 'Rabu' | 'Kamis' | 'Jumat' | 'Sabtu';
  jam_mulai: string;
  jam_selesai: string;
  ruangan: string | null;
  tahun_ajaran?: {
    id: number;
    nama_tahun_ajaran: string;
    semester: string;
  } | null;
  kelas?: {
    id: number;
    nama_kelas: string;
  } | null;
  teacher?: {
    id: number;
    nama: string;
    nip: string;
  } | null;
  subject?: {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
  } | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type TeacherSubjectItem = {
  id: number;
  teacher_id: number;
  subject_id: number;
  teacher?: {
    id: number;
    nama: string;
    nip: string;
    email: string;
  } | null;
  subject?: {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
  } | null;
};

export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
  email?: string | null;
};

export type SubjectOption = {
  id: number;
  kode_mapel: string;
  nama_mapel: string;
};

export type ClassroomOption = {
  id: number;
  nama_kelas: string;
  tingkat?: number;
  rombel?: string;
};

export type AcademicYearOption = {
  id: number;
  nama_tahun_ajaran: string;
  semester: string;
  status_aktif: boolean;
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
