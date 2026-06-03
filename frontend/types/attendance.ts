export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
  email?: string | null;
};

export type ClassroomOption = {
  id: number;
  nama_kelas: string;
  tingkat?: number;
  rombel?: string;
};

export type StudentOption = {
  id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  kelas_id: number | null;
  status_aktif: boolean;
  kelas?: {
    id: number;
    nama_kelas: string;
  } | null;
};

export type AttendanceItem = {
  id: number;
  siswa_id: number;
  tanggal: string;
  status: 'hadir' | 'izin' | 'sakit' | 'alfa';
  keterangan: string | null;
  guru_id: number;
  student?: {
    id: number;
    nis: string;
    nama_lengkap: string;
    kelas_id: number | null;
    kelas?: {
      id: number;
      nama_kelas: string;
    } | null;
  } | null;
  teacher?: {
    id: number;
    nama: string;
    nip: string;
  } | null;
};

export type AttendanceRecapStudent = {
  siswa_id: number;
  student?: {
    id: number;
    nis: string;
    nama_lengkap: string;
    kelas?: {
      id: number;
      nama_kelas: string;
    } | null;
  } | null;
  hadir: number;
  izin: number;
  sakit: number;
  alfa: number;
};

export type AttendanceRecapResponse = {
  summary: {
    hadir: number;
    izin: number;
    sakit: number;
    alfa: number;
  };
  students: AttendanceRecapStudent[];
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
