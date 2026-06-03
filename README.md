# Sistem Informasi Sekolah

Monorepo MVP sistem informasi sekolah berbasis:

- `backend/`: Laravel REST API + PostgreSQL
- `frontend/`: Nuxt 3 + Pinia + TailwindCSS

Dokumen arsitektur lengkap tersedia di `docs/architecture.md`.

## Struktur

```text
management-sekolah/
├── backend/
├── frontend/
└── docs/
```

## Fokus MVP

- Authentication & role-based access (`admin`, `guru`)
- Master data siswa, orang tua, guru, kelas, tahun ajaran, mata pelajaran
- Absensi harian
- Penilaian
- Generate rapot
- Dashboard admin dan guru

## Next step

1. Jalankan instalasi dependency Laravel dan Nuxt
2. Konfigurasi `.env`
3. Jalankan migration + seeder
4. Bangun modul per fitur berdasarkan kontrak API
