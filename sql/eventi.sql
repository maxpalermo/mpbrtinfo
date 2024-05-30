-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 30, 2024 alle 10:31
-- Versione del server: 10.6.16-MariaDB-0ubuntu0.22.04.1
-- Versione PHP: 8.1.28

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `PFX_mpbrtinfo_evento`
--

DROP TABLE IF EXISTS `PFX_mpbrtinfo_evento`;

CREATE TABLE IF NOT EXISTS `PFX_mpbrtinfo_evento` (
    `id_mpbrtinfo_evento` int(11) NOT NULL AUTO_INCREMENT,
    `id_evento` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `is_error` tinyint(1) DEFAULT NULL,
    `is_transit` tinyint(1) DEFAULT NULL,
    `is_delivered` tinyint(1) DEFAULT NULL,
    `is_fermopoint` tinyint(1) DEFAULT NULL,
    `is_waiting` tinyint(1) DEFAULT NULL,
    `is_refused` tinyint(1) DEFAULT NULL,
    `is_sent` tinyint(1) DEFAULT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_mpbrtinfo_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Svuota la tabella prima dell'inserimento `PFX_mpbrtinfo_evento`
--

TRUNCATE TABLE `PFX_mpbrtinfo_evento`;
--
-- Dump dei dati per la tabella `PFX_mpbrtinfo_evento`
--

INSERT INTO `PFX_mpbrtinfo_evento` (`id_mpbrtinfo_evento`, `id_evento`, `name`, `is_error`, `is_transit`, `is_delivered`, `is_fermopoint`, `is_waiting`, `is_refused`, `is_sent`, `date_add`, `date_upd`) VALUES
(1, 'AVV', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:15', '2023-12-12 19:01:33'),
(2, 'AV2', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(3, 'AV3', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(4, 'AV5', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(5, 'AV7', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(6, 'AV9', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(7, 'A16', 'RIMANDA LA CONSEGNA', 1, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(8, 'A23', 'DESTINATARIO CHIUSO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(9, 'DDB', 'DOCUMENTI DOGANALI MANCANTI', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(10, 'DDC', 'DISTRUTTA/REQUISITA DA DOGANA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(11, 'DDR', 'RIFIUTA:NON PAGA DAZI DOGANALI', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(12, 'DDS', 'FERMA PER CONTROLLI DOGANALI', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(13, 'DIR', 'INOLTRO ALTRA FILIALE', 0, 1, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(14, 'DPP', 'NON RITIRATA AL PARCEL SHOP', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(15, 'DPU', 'DA RITIRARE AL PARCEL SHOP', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(16, 'G', 'IN GIACENZA', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(17, 'GEN', 'IN GIACENZA', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(18, 'G02', 'IN ATTESA APERTURA GIACENZA 2G', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(19, 'G03', 'IN ATTESA APERTURA GIACENZA 3G', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(20, 'G05', 'IN ATTESA APERTURA GIACENZA 5G', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(21, 'G09', 'IN ATTESA APERTURA GIACENZA 9G', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(22, 'IDD', 'CONTATTARE FILIALE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(23, 'MAN', 'COLLO/I MANCANTE/I', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(24, 'MIC', 'IN CONSEGNA', 0, 1, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(25, 'N', 'DA CONSEGNARE', 0, 1, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(26, 'P', 'CONSEGNATA PARZIALMENTE', 1, 0, 1, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(27, 'PAT', 'FESTIVITA\' PATRONALE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(28, 'RIC', 'Destin.Assente:LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(29, 'SIP', 'REINDIRIZZATA A BRT-fermopoint', 0, 1, 0, 1, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(30, 'T', 'CHIUSO PER TURNO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(31, 'ZAC', 'ACQUA ALTA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:07'),
(32, 'ZAL', 'ALLUVIONE/NUBIFRAGIO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(33, 'ZBC', 'BLOCCO CIRCOLAZIONE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(34, 'ZBS', 'BLOCCO STRADALE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(35, 'ZDM', 'DISAGI DOPO MANIFESTAZIONE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(36, 'ZEE', 'INTERRUZIONE ENERGIA ELETTRICA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(37, 'ZFM', 'CAUSA FORZA MAGGIORE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(38, 'ZFR', 'FESTIVITA REGIONALE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(39, 'ZMM', 'MARE MOLTO MOSSO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(40, 'ZMP', 'MANIFESTAZIONE PUBBLICA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(41, 'ZMS', 'MANIFESTAZIONE SPORTIVA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(42, 'ZNV', 'NEVICATA ECCEZIONALE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(43, 'ZSC', 'SCIOPERO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(44, 'ZSF', 'GIORNATA SEMI-FESTIVA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(45, 'ZTR', 'TERREMOTO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(46, '001', 'RIFIUTA SENZA MOTIVAZIONE', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(47, '002', 'RIFIUTA:MERCE NON ORDINATA', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(48, '003', 'RIFIUTA:SPEDITA IN RITARDO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(49, '004', 'RIFIUTA:MERCE GIA\' RICEVUTA', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(50, '005', 'RIFIUTA:SPEDITA IN ANTICIPO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(51, '006', 'RIFIUTA:RESO NON AUTORIZZATO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(52, '007', 'RIFIUTO PER COLLO DANNEGGIATO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(53, '008', 'RIFIUTA:NON RICEVE C/ASSEGNO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(54, '009', 'RIFIUTA:CHIEDE CONTROLLO MERCE', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(55, '012', 'RIFIUTA:NON PAGA TRASPORTO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(56, '016', 'RIMANDA LA CONSEGNA', 1, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(57, '017', 'ASSENTE DOPO LASCIATO AVVISO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(58, '019', 'CESSATA ATTIVITA\' DESTINATARIO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(59, '021', 'DESTINATAR.SCONOSC./INCOMPLETO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(60, '022', 'INDIRIZ.INESISTENTE/INCOMPLETO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(61, '023', 'DESTINATARIO CHIUSO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(62, '024', 'DESTINATARIO CHIUSO PER FERIE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(63, '026', 'CHIESTA CONSEGNA ALTRO INDIR.', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(64, '027', 'SPEDIZIONE IN TRANSITO', 0, 1, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(65, '028', 'ESERCIZIO NON IN ATTIVITA\'', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(66, '032', 'FERMO DEPOSITO:NESSUNO RITIRA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(67, '034', 'NON CONSEGNAB.FORZA MAGGIORE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(68, '035', 'DOCUMENTI INCOMPLETI/MANCANTI', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(69, '037', 'RIFIUTA CONSEGNA TASSATIVA', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(70, '044', 'PINCODE ERRATO O MANCANTE', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(71, '045', 'DATI MANCANTI PER LA FATTURA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(72, '050', 'RIFIUTATA DAL BRT-fermopoint', 0, 0, 0, 1, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(73, '051', 'RIFIUTATA DAL DESTINATARIO', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(74, '052', 'SCADUTI TERMINI PER IL RITIRO', 1, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(75, '055', 'LOCKER GUASTO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(76, '056', 'LOCKER PIENO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(77, '100', 'RIMANDA LA CONSEGNA', 1, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(78, '101', 'RIMANDA LA CONSEGNA', 1, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(79, '700', 'DATI SPEDIZ. TRASMESSI A BRT', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(80, '701', 'RITIRATA', 0, 0, 0, 0, 1, 0, 1, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(81, '702', 'PARTITA', 0, 0, 0, 0, 0, 0, 1, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(82, '703', 'ARRIVATA IN FILIALE', 0, 0, 0, 0, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(83, '704', 'CONSEGNATA', 0, 0, 1, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(84, '707', 'INOLTRO ALTRA FILIALE', 0, 1, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(85, '708', 'RESO AL MITTENTE', 0, 0, 0, 0, 0, 1, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(86, '709', 'PROPOSTA LIQUID.NE TRANSATTIVA', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(87, '710', 'PREAVVISO DI DANNO', 1, 0, 0, 0, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(88, '722', 'ARRIVATA AL BRT-fermopoint', 0, 0, 0, 1, 1, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(89, '724', 'RITIRATA AL BRT-fermopoint', 0, 0, 1, 1, 0, 0, 0, '2023-12-12 18:07:20', '2023-12-12 19:01:33'),
(90, 'ZRO', 'RALLENTAMENTI OPERATIVI', 1, 0, 0, 0, 0, 0, 0, '2024-05-19 18:03:17', '2024-05-19 18:03:17');
SET FOREIGN_KEY_CHECKS=1;
COMMIT;