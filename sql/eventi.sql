-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Mar 17, 2025 alle 10:28
-- Versione del server: 10.6.18-MariaDB-0ubuntu0.22.04.1-log
-- Versione PHP: 8.1.32
SET
    FOREIGN_KEY_CHECKS = 0;

SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

--
-- Database: `ps_dl_80`
--
-- --------------------------------------------------------
--
-- Struttura della tabella `ps_mpbrtinfo_evento`
--
DROP TABLE IF EXISTS `PFX_mpbrtinfo_evento`;

CREATE TABLE IF NOT EXISTS `PFX_mpbrtinfo_evento` (
    `id_mpbrtinfo_evento` int(11) NOT NULL AUTO_INCREMENT,
    `id_evento` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `id_order_state` int(11) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `icon` varchar(32) DEFAULT NULL,
    `color` char(7) DEFAULT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_mpbrtinfo_evento`)
) ENGINE = InnoDB AUTO_INCREMENT = 91 DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_general_ci;

--
-- Dump dei dati per la tabella `ps_mpbrtinfo_evento`
--
INSERT INTO
    `PFX_mpbrtinfo_evento` (
        `id_mpbrtinfo_evento`,
        `id_evento`,
        `name`,
        `id_order_state`,
        `email`,
        `icon`,
        `color`,
        `date_add`,
        `date_upd`
    )
VALUES
    (
        1,
        'AVV',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:15',
        '2025-03-15 18:54:26'
    ),
    (
        2,
        'AV2',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        3,
        'AV3',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        4,
        'AV5',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 19:00:33'
    ),
    (
        5,
        'AV7',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        6,
        'AV9',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        7,
        'A16',
        'RIMANDA LA CONSEGNA',
        187,
        'rimanda-consegna.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:51:20'
    ),
    (
        8,
        'A23',
        'DESTINATARIO CHIUSO',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        9,
        'DDB',
        'DOCUMENTI DOGANALI MANCANTI',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        10,
        'DDC',
        'DISTRUTTA/REQUISITA DA DOGANA',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        11,
        'DDR',
        'RIFIUTA:NON PAGA DAZI DOGANALI',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:50:59'
    ),
    (
        12,
        'DDS',
        'FERMA PER CONTROLLI DOGANALI',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        13,
        'DIR',
        'INOLTRO ALTRA FILIALE',
        182,
        '',
        'local_shipping',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:46:13'
    ),
    (
        14,
        'DPP',
        'NON RITIRATA AL PARCEL SHOP',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        15,
        'DPU',
        'DA RITIRARE AL PARCEL SHOP',
        187,
        'giacenza.html',
        'local_shipping',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:45:22'
    ),
    (
        16,
        'G',
        'IN GIACENZA',
        187,
        'giacenza.html',
        'inventory',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-15 21:45:57'
    ),
    (
        17,
        'GEN',
        'IN GIACENZA',
        187,
        'giacenza.html',
        'inventory',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-15 21:46:04'
    ),
    (
        18,
        'G02',
        'IN ATTESA APERTURA GIACENZA 2G',
        186,
        'giacenza.html',
        'schedule',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        19,
        'G03',
        'IN ATTESA APERTURA GIACENZA 3G',
        186,
        'giacenza.html',
        'schedule',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        20,
        'G05',
        'IN ATTESA APERTURA GIACENZA 5G',
        186,
        'giacenza.html',
        'schedule',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        21,
        'G09',
        'IN ATTESA APERTURA GIACENZA 9G',
        186,
        'giacenza.html',
        'schedule',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        22,
        'IDD',
        'CONTATTARE FILIALE',
        186,
        'errore.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 18:52:04'
    ),
    (
        23,
        'MAN',
        'COLLO/I MANCANTE/I',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        24,
        'MIC',
        'IN CONSEGNA',
        182,
        'in-consegna.html',
        'outbox',
        '#815643',
        '2023-12-12 18:07:20',
        '2025-03-15 21:45:49'
    ),
    (
        25,
        'N',
        'DA CONSEGNARE',
        182,
        'in-consegna.html',
        'pending',
        '#814613',
        '2023-12-12 18:07:20',
        '2025-03-15 21:45:12'
    ),
    (
        26,
        'P',
        'CONSEGNATA PARZIALMENTE',
        183,
        'consegnato.html',
        'check_circle',
        '#4CAF50',
        '2023-12-12 18:07:20',
        '2025-03-15 21:44:57'
    ),
    (
        27,
        'PAT',
        'FESTIVITA\' PATRONALE',
        186,
        'errore.html',
        'event',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        28,
        'RIC',
        'Destin.Assente:LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:07:58'
    ),
    (
        29,
        'SIP',
        'REINDIRIZZATA A BRT-fermopoint',
        182,
        'fermopoint-reindirizzato.html',
        'local_shipping',
        '#FB8C00',
        '2023-12-12 18:07:20',
        '2025-03-15 21:46:54'
    ),
    (
        30,
        'T',
        'CHIUSO PER TURNO',
        186,
        'errore.html',
        'schedule',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-15 18:44:17'
    ),
    (
        31,
        'ZAC',
        'ACQUA ALTA',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-15 21:42:46'
    ),
    (
        32,
        'ZAL',
        'ALLUVIONE/NUBIFRAGIO',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-15 21:43:02'
    ),
    (
        33,
        'ZBC',
        'BLOCCO CIRCOLAZIONE',
        186,
        'errore.html',
        'block',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-15 21:07:03'
    ),
    (
        34,
        'ZBS',
        'BLOCCO STRADALE',
        186,
        'errore.html',
        'block',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-15 18:42:35'
    ),
    (
        35,
        'ZDM',
        'DISAGI DOPO MANIFESTAZIONE',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        36,
        'ZEE',
        'INTERRUZIONE ENERGIA ELETTRICA',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        37,
        'ZFM',
        'CAUSA FORZA MAGGIORE',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-15 18:50:32'
    ),
    (
        38,
        'ZFR',
        'FESTIVITA REGIONALE',
        186,
        'errore.html',
        'event',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        39,
        'ZMM',
        'MARE MOLTO MOSSO',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        40,
        'ZMP',
        'MANIFESTAZIONE PUBBLICA',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        41,
        'ZMS',
        'MANIFESTAZIONE SPORTIVA',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        42,
        'ZNV',
        'NEVICATA ECCEZIONALE',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        43,
        'ZSC',
        'SCIOPERO',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        44,
        'ZSF',
        'GIORNATA SEMI-FESTIVA',
        186,
        'errore.html',
        'event',
        '#9E9E9E',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        45,
        'ZTR',
        'TERREMOTO',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        46,
        '001',
        'RIFIUTA SENZA MOTIVAZIONE',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:12'
    ),
    (
        47,
        '002',
        'RIFIUTA:MERCE NON ORDINATA',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:31'
    ),
    (
        48,
        '003',
        'RIFIUTA:SPEDITA IN RITARDO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:07'
    ),
    (
        49,
        '004',
        'RIFIUTA:MERCE GIA\' RICEVUTA',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:25'
    ),
    (
        50,
        '005',
        'RIFIUTA:SPEDITA IN ANTICIPO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:02'
    ),
    (
        51,
        '006',
        'RIFIUTA:RESO NON AUTORIZZATO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:57'
    ),
    (
        52,
        '007',
        'RIFIUTO PER COLLO DANNEGGIATO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:27'
    ),
    (
        53,
        '008',
        'RIFIUTA:NON RICEVE C/ASSEGNO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:52'
    ),
    (
        54,
        '009',
        'RIFIUTA:CHIEDE CONTROLLO MERCE',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:20'
    ),
    (
        55,
        '012',
        'RIFIUTA:NON PAGA TRASPORTO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:46'
    ),
    (
        56,
        '016',
        'RIMANDA LA CONSEGNA',
        187,
        'rimanda-consegna.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:46'
    ),
    (
        57,
        '017',
        'ASSENTE DOPO LASCIATO AVVISO',
        186,
        'avviso.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        58,
        '019',
        'CESSATA ATTIVITA\' DESTINATARIO',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 18:50:47'
    ),
    (
        59,
        '021',
        'DESTINATAR.SCONOSC./INCOMPLETO',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        60,
        '022',
        'INDIRIZ.INESISTENTE/INCOMPLETO',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        61,
        '023',
        'DESTINATARIO CHIUSO',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        62,
        '024',
        'DESTINATARIO CHIUSO PER FERIE',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        63,
        '026',
        'CHIESTA CONSEGNA ALTRO INDIR.',
        182,
        'transito.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:49:58'
    ),
    (
        64,
        '027',
        'SPEDIZIONE IN TRANSITO',
        182,
        'transito.html',
        'local_shipping',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:51:45'
    ),
    (
        65,
        '028',
        'ESERCIZIO NON IN ATTIVITA\'',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 19:05:44'
    ),
    (
        66,
        '032',
        'FERMO DEPOSITO:NESSUNO RITIRA',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        67,
        '034',
        'NON CONSEGNAB.FORZA MAGGIORE',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        68,
        '035',
        'DOCUMENTI INCOMPLETI/MANCANTI',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        69,
        '037',
        'RIFIUTA CONSEGNA TASSATIVA',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:47:05'
    ),
    (
        70,
        '044',
        'PINCODE ERRATO O MANCANTE',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        71,
        '045',
        'DATI MANCANTI PER LA FATTURA',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-15 18:52:14'
    ),
    (
        72,
        '050',
        'RIFIUTATA DAL BRT-fermopoint',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:15'
    ),
    (
        73,
        '051',
        'RIFIUTATA DAL DESTINATARIO',
        184,
        'rifiutato.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:21'
    ),
    (
        74,
        '052',
        'SCADUTI TERMINI PER IL RITIRO',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        75,
        '055',
        'LOCKER GUASTO',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        76,
        '056',
        'LOCKER PIENO',
        186,
        'errore.html',
        'error',
        '#EF5350',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        77,
        '100',
        'RIMANDA LA CONSEGNA',
        187,
        'rimanda-consegna.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:52'
    ),
    (
        78,
        '101',
        'RIMANDA LA CONSEGNA',
        187,
        'rimanda-consegna.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-15 21:48:57'
    ),
    (
        79,
        '700',
        'DATI SPEDIZ. TRASMESSI A BRT',
        0,
        '',
        'receipt_long',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:50:14'
    ),
    (
        80,
        '701',
        'RITIRATA',
        0,
        '',
        'shopping_cart_checkout',
        '#8C6F50',
        '2023-12-12 18:07:20',
        '2025-03-15 22:34:01'
    ),
    (
        81,
        '702',
        'PARTITA',
        181,
        'partita.html',
        'local_shipping',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:46:38'
    ),
    (
        82,
        '703',
        'ARRIVATA IN FILIALE',
        187,
        '',
        'store',
        '#4CAF50',
        '2023-12-12 18:07:20',
        '2025-03-15 21:44:25'
    ),
    (
        83,
        '704',
        'CONSEGNATA',
        183,
        'consegnato.html',
        'check_circle',
        '#4CAF50',
        '2023-12-12 18:07:20',
        '2025-03-15 21:44:51'
    ),
    (
        84,
        '707',
        'INOLTRO ALTRA FILIALE',
        182,
        '',
        'local_shipping',
        '#2196F3',
        '2023-12-12 18:07:20',
        '2025-03-15 21:46:21'
    ),
    (
        85,
        '708',
        'RESO AL MITTENTE',
        186,
        'errore.html',
        'cancel',
        '#D32F2F',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        86,
        '709',
        'PROPOSTA LIQUID.NE TRANSATTIVA',
        186,
        'errore.html',
        'info',
        '#FFEB3B',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        87,
        '710',
        'PREAVVISO DI DANNO',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2023-12-12 18:07:20',
        '2025-03-14 12:05:06'
    ),
    (
        88,
        '722',
        'ARRIVATA AL BRT-fermopoint',
        187,
        'fermopoint-arrivato.html',
        'local_shipping',
        '#FB8C00',
        '2023-12-12 18:07:20',
        '2025-03-15 21:49:35'
    ),
    (
        89,
        '724',
        'RITIRATA AL BRT-fermopoint',
        185,
        'fermopoint-ritirato.html',
        'local_shipping',
        '#FB8C00',
        '2023-12-12 18:07:20',
        '2025-03-15 21:49:12'
    ),
    (
        90,
        'ZRO',
        'RALLENTAMENTI OPERATIVI',
        186,
        'errore.html',
        'warning',
        '#FFA726',
        '2024-05-19 18:03:17',
        '2025-03-14 12:05:06'
    );

SET
    FOREIGN_KEY_CHECKS = 1;

COMMIT;