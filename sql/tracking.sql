-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 31, 2024 alle 15:32
-- Versione del server: 10.6.16-MariaDB-0ubuntu0.22.04.1
-- Versione PHP: 8.1.28

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `PFX_mpbrtinfo_tracking_number`
--

DROP TABLE IF EXISTS `PFX_mpbrtinfo_tracking_number`;
CREATE TABLE IF NOT EXISTS `PFX_mpbrtinfo_tracking_number` (
  `id_mpbrtinfo_tracking_number` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_order_state` int(11) NOT NULL,
  `date_event` datetime DEFAULT NULL,
  `id_brt_state` varchar(16) NULL,
  `id_collo` varchar(128) DEFAULT NULL,
  `rmn` int(11) DEFAULT NULL,
  `rma` varchar(128) DEFAULT NULL,
  `tracking_number` varchar(128) DEFAULT NULL,
  `current_state` varchar(255) DEFAULT NULL,
  `anno_spedizione` int(11) NOT NULL,
  `date_shipped` datetime DEFAULT NULL,
  `date_delivered` datetime DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mpbrtinfo_tracking_number`),
  KEY `idx_brt_tracking_id_order` (`id_order`),
  KEY `idx_brt_tracking_id_order_state` (`id_order_state`),
  KEY `idx_brt_tracking_id_brt_state` (`id_brt_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
