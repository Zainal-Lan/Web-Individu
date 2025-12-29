-- Buat database (opsional, jika belum ada)
CREATE DATABASE IF NOT EXISTS surat_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE surat_app;

-- Tabel pengguna
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin','pegawai') NOT NULL DEFAULT 'pegawai'
);

-- Tabel surat masuk
CREATE TABLE IF NOT EXISTS mail_in (
  id INT AUTO_INCREMENT PRIMARY KEY,
  no_agenda   VARCHAR(50),
  nomor_surat VARCHAR(100),
  pengirim    VARCHAR(150),
  perihal     VARCHAR(255),
  tgl_surat   DATE,
  tgl_terima  DATE,
  file        VARCHAR(255),
  status      ENUM('belum_dibaca','proses','selesai') DEFAULT 'belum_dibaca',
  created_by  INT,
  created_at  DATETIME,
  CONSTRAINT fk_mailin_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel surat keluar
CREATE TABLE IF NOT EXISTS mail_out (
  id INT AUTO_INCREMENT PRIMARY KEY,
  no_agenda   VARCHAR(50),
  nomor_surat VARCHAR(100),
  tujuan      VARCHAR(150),
  perihal     VARCHAR(255),
  tgl_surat   DATE,
  file        VARCHAR(255),
  status      ENUM('belum_dibaca','proses','selesai') DEFAULT 'belum_dibaca',
  created_by  INT,
  created_at  DATETIME,
  CONSTRAINT fk_mailout_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel disposisi
CREATE TABLE IF NOT EXISTS disposisi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mail_type ENUM('masuk','keluar') NOT NULL,
  mail_id   INT NOT NULL,
  kepada    VARCHAR(150),
  pesan     TEXT,
  status    ENUM('belum_dibaca','proses','selesai') DEFAULT 'belum_dibaca',
  catatan   TEXT NULL,
  created_by INT,
  created_at DATETIME,
  CONSTRAINT fk_disposisi_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- User default: admin / pegawai (password: 123456)
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin',   '$2b$12$9t2xy17xNb90TgPbA2HmZO7G3.UoTdmXvMF0aN4wf/c4FBB5EHU06', 'Administrator', 'admin'),
('pegawai', '$2b$12$9t2xy17xNb90TgPbA2HmZO7G3.UoTdmXvMF0aN4wf/c4FBB5EHU06', 'Pegawai Contoh', 'pegawai');
