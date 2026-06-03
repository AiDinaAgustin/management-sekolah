export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
};

export type LessonScheduleCard = {
  schedule_id: number;
  hari: string;
  jam_mulai: string;
  jam_selesai: string;
  ruangan?: string | null;
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
  academic_year?: {
    id: number;
    nama_tahun_ajaran: string;
    semester: string;
  } | null;
  meeting?: {
    id: number;
    pertemuan_ke: number;
    topik?: string | null;
    catatan?: string | null;
    status: string;
    attendance_count: number;
  } | null;
};

export type LessonMeetingDetail = {
  id: number;
  tanggal: string;
  pertemuan_ke: number;
  topik?: string | null;
  catatan?: string | null;
  status: string;
  schedule: {
    id: number;
    hari: string;
    jam_mulai: string;
    jam_selesai: string;
    ruangan?: string | null;
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
    academic_year?: {
      id: number;
      nama_tahun_ajaran: string;
      semester: string;
    } | null;
  };
};

export type LessonAttendanceRow = {
  id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  kelas?: {
    id: number;
    nama_kelas: string;
  } | null;
  attendance?: {
    id: number;
    status: 'hadir' | 'izin' | 'sakit' | 'alfa';
    keterangan?: string | null;
  } | null;
};
