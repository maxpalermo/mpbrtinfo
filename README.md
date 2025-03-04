# MP BRT Info - Modulo di Tracking & Delivery per PrestaShop

## Descrizione
MP BRT Info è un modulo avanzato per PrestaShop che consente di gestire automaticamente il tracking e i risultati di consegna delle spedizioni effettuate tramite il corriere BRT (Bartolini). Il modulo utilizza il servizio SOAP di BRT per recuperare informazioni sullo stato delle spedizioni e aggiornare automaticamente lo stato degli ordini in PrestaShop.

## Caratteristiche Principali
- Integrazione con l'API SOAP di BRT per il tracking delle spedizioni
- Aggiornamento automatico dello stato degli ordini in base agli eventi di consegna
- Visualizzazione delle informazioni di tracking direttamente nel back-office di PrestaShop
- Creazione automatica di stati ordine personalizzati per BRT (Spedito, Transito, Consegnato, ecc.)
- Supporto per diversi metodi di ricerca delle spedizioni (RMN, RMA, ID Collo)
- Dashboard con statistiche sulle consegne
- Compatibile con PrestaShop 1.8.0 e versioni successive

## Requisiti
- PrestaShop 1.8.0 o superiore
- PHP 7.0 o superiore con estensione SOAP abilitata
- Accesso ai servizi SOAP di BRT (credenziali cliente BRT)
- Modulo Carrier configurato per BRT

## Installazione
1. Scarica il file ZIP del modulo
2. Vai al back-office di PrestaShop > Moduli > Carica un modulo
3. Seleziona il file ZIP scaricato
4. Una volta installato, il modulo creerà automaticamente gli stati ordine necessari e le tabelle nel database

## Configurazione
1. Accedi al back-office di PrestaShop
2. Vai a Moduli > Gestione Moduli
3. Trova "MP BRT Tracking & Delivery results with SOAP" e clicca su "Configura"

### Impostazioni Principali
- **ID Cliente BRT**: Inserisci l'ID cliente fornito da BRT
- **Corrieri BRT**: Seleziona i corrieri associati a BRT nel tuo negozio
- **Usa SSL**: Attiva/disattiva la connessione sicura per le chiamate SOAP
- **Tipo di ricerca**: Scegli il metodo di ricerca delle spedizioni (RMN, RMA, ID)
- **Dove cercare**: Specifica se cercare per ID ordine o riferimento ordine
- **Eventi di stato**: Configura quali eventi BRT corrispondono ai vari stati degli ordini

## Utilizzo
Una volta configurato, il modulo funziona automaticamente:

1. Quando un ordine viene spedito con BRT, il modulo inizia a tracciare la spedizione
2. Gli stati degli ordini vengono aggiornati automaticamente in base agli eventi di consegna ricevuti da BRT
3. Gli amministratori possono visualizzare le informazioni di tracking direttamente nella pagina dell'ordine
4. Il modulo può essere configurato per eseguire controlli periodici tramite cron job

### Bulk Actions
Per aggiungere un separatore nelle bulk actions, utilizzare la seguente sintassi:
```php
'divider1' => [
    'text' => 'divider'
]
```

## Supporto
Per assistenza o informazioni aggiuntive, contattare:
- Autore: Massimiliano Palermo
- Email: maxx.palermo@gmail.com

## Licenza
Academic Free License version 3.0
