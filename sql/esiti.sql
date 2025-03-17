-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 30, 2024 alle 10:33
-- Versione del server: 10.6.16-MariaDB-0ubuntu0.22.04.1
-- Versione PHP: 8.1.28
SET
    FOREIGN_KEY_CHECKS = 0;

SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

-- --------------------------------------------------------
--
-- Struttura della tabella `PFX_mpbrtinfo_esito`
--
DROP TABLE IF EXISTS `PFX_mpbrtinfo_esito`;

CREATE TABLE IF NOT EXISTS `PFX_mpbrtinfo_esito` (
    `id_mpbrtinfo_esito` int(11) NOT NULL AUTO_INCREMENT,
    `id_esito` varchar(255) NOT NULL,
    `testo1` varchar(255) NOT NULL,
    `testo2` varchar(255) DEFAULT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_mpbrtinfo_esito`)
) ENGINE = InnoDB AUTO_INCREMENT = 15 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Svuota la tabella prima dell'inserimento `PFX_mpbrtinfo_esito`
--
TRUNCATE TABLE `PFX_mpbrtinfo_esito`;

--
-- Dump dei dati per la tabella `PFX_mpbrtinfo_esito`
--
INSERT INTO
    `PFX_mpbrtinfo_esito` (
        `id_mpbrtinfo_esito`,
        `id_esito`,
        `testo1`,
        `testo2`,
        `date_add`,
        `date_upd`
    )
VALUES
    (
        1,
        '0',
        'Elaborazione eseguita con successo.',
        '',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        2,
        '-1',
        'Errore sconosciuto o non previsto.',
        'Se il problema persiste mailto:ced@brt.it',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        3,
        '2',
        'Lingua sconosciuta o non gestita. I dati sono stati serviti in lingua italiana.',
        'Le lingue gestite sono: it (italiano), en (inglese), fr (francese), de (tedesco).',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        4,
        '-3',
        'In questo momento non è possibile connettersi al database server.',
        'Se il problema persiste mailto:ced@brt.it',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        5,
        '-4',
        'Parametro obbligatorio non passato',
        '',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        6,
        '-5',
        'Valore parametro non valido',
        '',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        7,
        '-10',
        'ID spedizione obbligatorio o non valido.',
        'L\'ID spedizione è un numero positivo di 12 cifre.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        8,
        '-11',
        'Spedizione non trovata.',
        '',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        9,
        '-20',
        'Riferimento mittente numerico obbligatorio o non valido.',
        'Il riferimento mittente numerico è un numero positivo di massimo 15 cifre.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        10,
        '-21',
        'ID cliente obbligatorio o non valido.',
        'L\'ID cliente è un numero positivo di 7 cifre.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        11,
        '-22',
        'Riferimento non univoco.',
        'Con il riferimento richiesto è stata trovata più di una spedizione.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        12,
        '-23',
        'Riferimento mittente alfabetico obbligatorio.',
        'Il riferimento mittente alfabetico è una stringa di massimo 15 caratteri.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        13,
        '-30',
        'ID collo obbligatorio.',
        'L\'ID collo è una stringa di massimo 35 caratteri.',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    ),
    (
        14,
        '100',
        'Dati finiti.',
        '',
        '2023-12-15 12:49:56',
        '2023-12-15 12:49:56'
    );

SET
    FOREIGN_KEY_CHECKS = 1;

COMMIT;