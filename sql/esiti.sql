TRUNCATE TABLE `PFX_mpbrtinfo_esito`;

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
