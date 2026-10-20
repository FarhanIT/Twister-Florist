-- Database Twister Florist
CREATE DATABASE IF NOT EXISTS twister_florist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE twister_florist;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(80) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nama VARCHAR(120) NOT NULL,
  harga INT UNSIGNED NOT NULL DEFAULT 0,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  gambar VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE penjualan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produk_id INT NULL,
  nama_produk VARCHAR(120) NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  harga_satuan INT UNSIGNED NOT NULL,
  total INT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  catatan VARCHAR(255) NULL,
  user_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Data contoh
INSERT INTO kategori (nama) VALUES ('Romantis'),('Custom'),('Wisuda'),('Ulang Tahun'),('Bunga');
INSERT INTO produk (kategori_id,nama,harga,stok) VALUES
 (1,'Buket Mawar',150000,15),
 (2,'Buket Sunflower',180000,2),
 (3,'Buket Wisuda',200000,9),
 (4,'Buket Ulang Tahun',175000,4),
 (5,'Bunga Krisan',120000,0),
 (2,'Bucket Mini',90000,3);
