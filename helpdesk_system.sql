-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Des 2025 pada 17.20
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `helpdesk_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'IT', 'Masalah terkait teknologi informasi dan komputer', '2025-10-17 06:13:04'),
(2, 'Fasilitas', 'Permasalahan fasilitas kantor', '2025-10-17 06:13:04'),
(3, 'HR', 'Sumber daya manusia dan kepegawaian', '2025-10-17 06:13:04'),
(4, 'Keuangan', 'Pembayaran dan keuangan', '2025-10-17 06:13:04'),
(5, 'Umum', 'Pertanyaan atau permintaan umum', '2025-10-17 06:13:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Open','Diproses','Selesai') DEFAULT 'Open',
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `title`, `description`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 'printer rusak', 'au tiba tiba rusak', 'Selesai', 'Medium', '2025-10-17 06:20:56', '2025-10-17 06:35:58'),
(2, 2, 3, 'maslaah', 'yak hahahahaaaaaaaaaaaaaaaaaaaaaaaa', 'Diproses', 'High', '2025-10-20 09:51:55', '2025-10-20 09:53:54'),
(3, 2, 1, 'PC RUSKA', 'rusak au ngapa', 'Selesai', 'High', '2025-10-21 02:38:08', '2025-10-21 02:39:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_comments`
--

CREATE TABLE `ticket_comments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ticket_comments`
--

INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 2, 'benerin kek', '2025-10-17 06:21:05'),
(2, 1, 1, 'ok', '2025-10-17 06:24:26'),
(3, 2, 2, 'gc', '2025-10-20 09:52:01'),
(4, 2, 1, 'y', '2025-10-20 09:52:25'),
(5, 2, 2, 'yowes', '2025-10-20 09:52:45'),
(6, 2, 1, 'dah y', '2025-10-20 09:52:59'),
(7, 2, 2, 'mantap', '2025-10-20 09:53:32'),
(8, 2, 2, 'belom tuh', '2025-10-20 09:53:38'),
(9, 2, 1, 'y', '2025-10-20 09:53:54'),
(10, 3, 1, 'y gw kerjain', '2025-10-21 02:38:46'),
(11, 3, 2, 'oks', '2025-10-21 02:38:53'),
(12, 3, 1, 'dh y msh', '2025-10-21 02:39:08'),
(13, 3, 2, 'mksh', '2025-10-21 02:39:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin', '2025-10-17 06:13:04'),
(2, 'dimas', '$2y$10$IkZCoSrJv2xx8GTzTQPId.WWqfN1/y/CfPOt0uKXYOWAczPv2Odse', 'indra', 'dimas.indra773@gmail.com', 'user', '2025-10-17 06:20:28'),
(3, 'irgi_04', '$2y$10$ryseJLRM3gfbiAwOnGjb3eKIs7rrN8MpKQW3a1kyC4UsUL0IASaO2', 'irgi', 'iya@gmail.com', 'user', '2025-10-21 02:00:09'),
(4, 'didi', '$2y$10$uXU9brTUXkb4OZNE.1rK4O1T13RhFU.IF8vNMEzR0buP9vxydoNPy', 'didi h', 'didi@gmail.com', 'user', '2025-10-21 02:19:53');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `ticket_comments`
--
ALTER TABLE `ticket_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD CONSTRAINT `ticket_comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
