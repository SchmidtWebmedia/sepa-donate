# SEPA Donate for TYPO3

TYPO3 extension for integrating a SEPA donation form with EPC GiroCode QR code generation.

The extension provides a frontend plugin in which visitors can select a predefined donation amount or enter an individual amount. Optionally, contact details for a donation receipt can be entered. After submission, the extension generates an EPC QR code for the bank transfer together with a unique reference and can send a notification email to the configured recipient.

## Requirements

- TYPO3 13 LTS or TYPO3 14
- Composer-based TYPO3 installation

## Installation

Install the extension via Composer:

```bash
composer require schmidtwebmedia/sepa-donate
```

After installation, clear the TYPO3 caches if necessary.

## 1. Add the Site Set

The extension provides the TYPO3 Site Set:

```text
schmidtwebmedia/sepa-donate
```

Add **SEPA Donate** as a dependency to the Site Set used by your site. This loads the required TypoScript and makes the SEPA Donate site settings available.

## 2. Configure the Site Settings

Configure the following settings for your TYPO3 site:

| Setting | Description | Example |
| --- | --- | --- |
| `sepaDonate.iban` | IBAN of the donation account | `DE12345678901234567890` |
| `sepaDonate.bic` | BIC of the bank | `COBADEFFXXX` |
| `sepaDonate.recipient` | Name of the payment recipient | `Musterverein e.V.` |
| `sepaDonate.buttonBarAmounts` | Comma-separated predefined donation amounts | `10,25,50,100` |
| `sepaDonate.mail.to` | Recipient of donation notifications | `vorstand@musterverein.de` |

The IBAN and recipient are required for generating the EPC GiroCode. If no notification address is configured, no notification email is sent.

## 3. Add the Donation Form

Create a new content element on the page where the donation form should be displayed and select the plugin:

**SEPA Donate — Spendenformular**

The plugin renders the donation form and automatically includes the JavaScript and CSS required for its functionality.

Visitors can:

- select one of the configured donation amounts,
- enter an individual amount,
- optionally request a donation receipt and provide their contact details,
- generate an EPC GiroCode for the bank transfer.

The generated result contains the amount, recipient, IBAN, unique payment reference and QR code that can be scanned with a compatible banking app.

## Notification Emails

When a QR code is generated, the extension can send a notification email to the address configured in `sepaDonate.mail.to`.

The notification contains the donation amount and generated reference. If the visitor requested a donation receipt, the entered contact details are included as well.

The notification indicates a **possible donation** only. Generating the QR code does not confirm that the bank transfer was actually executed.

Mail delivery uses the TYPO3 mail configuration.

## Templates and Styling

The extension contains the complete functional frontend, but it can be adapted to the design of the integrating TYPO3 project.

The default template is located at:

```text
EXT:sepa_donate/Resources/Private/Templates/Donation/Form.html
```

Custom template paths can be configured in the SitePackage, for example:

```typoscript
plugin.tx_sepadonate {
    view {
        templateRootPaths.10 = EXT:site_package/Resources/Private/Templates/SepaDonate/
        partialRootPaths.10 = EXT:site_package/Resources/Private/Partials/SepaDonate/
        layoutRootPaths.10 = EXT:site_package/Resources/Private/Layouts/SepaDonate/
    }
}
```

The extension also ships with its own CSS. Project-specific styling can be added or the template and styles can be overridden in the SitePackage as required.

## How It Works

The frontend sends the entered donation data to the extension endpoint:

```text
/api/sepa-donate/qr-code
```

TYPO3 resolves the current site and the endpoint reads the SEPA configuration from its Site Settings. The extension then:

1. generates a unique donation reference,
2. creates an EPC GiroCode containing the configured account, amount and reference,
3. optionally sends a notification email,
4. returns the QR code and payment information to the frontend.

The actual payment is performed independently in the donor's banking application. The extension does not connect to a bank and does not verify incoming payments.

## License

GPL-2.0-or-later
