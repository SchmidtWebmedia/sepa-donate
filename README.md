# sepa_donate — TYPO3 SEPA Donation Extension

TYPO3 Extension für SEPA-Spenden mit EPC GiroCode QR-Generierung.

## Kompatibilität

- TYPO3 13 LTS
- TYPO3 14 LTS

## Installation

```bash
composer require schmidtwebmedia/sepa-donate
```

## Konfiguration

TypoScript im SitePackage:

```typoscript
plugin.tx_sepadonate {
    settings {
        iban        = DE12 3456 7890 1234 5678 90
        bic         = COBADEFFXXX
        empfaenger  = Musterverein e.V.
        betraege    = 10,25,50,100

        email {
            an      = vorstand@musterverein.de
            von     = noreply@musterverein.de
        }
    }
}
```

## Templates überschreiben

```typoscript
plugin.tx_sepadonate {
    view {
        templateRootPaths.10 = EXT:site_package/Resources/Private/Templates/SepaDonate/
        partialRootPaths.10  = EXT:site_package/Resources/Private/Partials/SepaDonate/
    }
}
```

## Styling

Die Extension liefert nur funktionales CSS (ein-/ausblenden).
Das Design übernimmt das SitePackage:

```scss
// EXT:site_package/Resources/Private/Scss/components/_sepa-donate.scss
.spenden-formular {
    // eigene Styles
}
```

## Aufbau

```
Classes/
├── Controller/DonationController.php
└── Service/
    ├── QrCodeService.php      — EPC GiroCode Generierung
    ├── ReferenceService.php   — Referenznummer SPENDE-YYYY-XXXXXX
    └── MailService.php        — Benachrichtigung an Verein

Resources/
├── Private/Templates/
│   ├── SepaDonate/Form.html
│   └── Email/Notification/SpendeEingegangen.txt
└── Public/
    ├── Css/sepa_donate.css
    └── JavaScript/sepa_donate.js
```
