Status 3 Tahap
==============
Untuk fitur Surat Masuk, Surat Keluar, dan Disposisi, digunakan status 3 tahap:
1. `belum_dibaca`
2. `proses`
3. `selesai`

Status bisa diubah melalui:
- Surat Masuk: kolom STATUS dalam tabel (dropdown).
- Surat Keluar: kolom STATUS dalam tabel (dropdown).
- Disposisi: dropdown status di tabel disposisi:
  - Admin: bisa ubah semua.
  - Pegawai: hanya bisa ubah disposisi yang KEPADA-nya berisi nama/username pegawai tersebut.

Pemisahan Fitur: Admin vs Pegawai
=================================

Admin
-----
- Kelola pengguna (tambah, hapus user).
- Tambah & hapus surat masuk / keluar.
- Tambah disposisi.
- Hapus disposisi.
- Ubah status surat masuk, surat keluar, dan semua disposisi.
- Lihat semua data.

Pegawai
-------
- Tambah surat masuk / keluar.
- Tidak bisa hapus surat.
- Tambah disposisi.
- Ubah status surat masuk & keluar (misalnya ketika sudah diproses).
- Ubah status + catatan disposisi yang ditujukan kepadanya.
- Tidak bisa hapus disposisi.
- Tidak bisa kelola pengguna.
