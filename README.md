# SEPA Donate for TYPO3

TYPO3 extension for integrating a SEPA donation form with EPC GiroCode QR code generation.

SEPA Donate provides a lightweight donation flow directly inside TYPO3. Visitors prepare a SEPA bank transfer by selecting a donation amount, optionally entering their details for a donation receipt and scanning the generated EPC GiroCode with their banking app. No external payment provider is required.

## Features

- **EPC GiroCode generation** for SEPA bank transfers that can be scanned with compatible banking apps.
- **Predefined donation amounts** configurable through TYPO3 Site Settings.
- **Individual donation amounts** entered directly by the visitor.
- **Unique payment purpose** generated for every prepared donation to make incoming transfers easier to identify.
- **Optional donation receipt details** including name, address and email address.
- **Notification emails** when a visitor prepares a donation, including receipt details when provided.
- **TYPO3 Site Set integration** for account and extension configuration.
- **Multi-site support** with configuration resolved from the current TYPO3 site.
- **Subdirectory support** by deriving the endpoint from the TYPO3 Site Base.
- **Customizable Fluid templates** so the form can be fully integrated into the design of a SitePackage.
- **Stable `data-sepa-*` JavaScript hooks**, allowing CSS classes and IDs to be changed in template overrides without breaking the frontend logic.
- **Multiple donation forms per page** supported by the JavaScript integration.
- **Built-in spam prevention without Captcha or external services**, including a hidden trap field, request rate limiting and short-lived single-use form tokens.
- **Same-origin protection** for browser requests when an Origin header is available.
- **No payment provider or bank API required.** The payment itself remains a normal SEPA transfer performed in the donor's banking application.
- **No external spam-protection service or additional frontend framework required.**

The extension deliberately does not process payments itself and does not verify incoming transfers. Generating a GiroCode means that a transfer has been prepared, not that a donation has been received.

## Requirements

- TYPO3 13 LTS or TYPO3 14
- Composer-based TYPO3 installation

# Integration

## 1. Installation

Install the extension via Composer:

```bash
composer require schmidtwebmedia/sepa-donate
```

After installation, clear the TYPO3 caches if necessary.

## 2. Add the Site Set

The extension provides the TYPO3 Site Set:

```text
schmidtwebmedia/sepa-donate
```

Add **SEPA Donate** as a dependency to the Site Set used by your site. This loads the required TypoScript and makes the SEPA Donate site settings available.

## 3. Configure the Site Settings

Configure the following settings for your TYPO3 site:

| Setting | Description | Example |
| --- | --- | --- |
| `sepaDonate.iban` | IBAN of the donation account | `DE12345678901234567890` |
| `sepaDonate.bic` | BIC of the bank | `COBADEFFXXX` |
| `sepaDonate.recipient` | Name of the payment recipient | `Musterverein e.V.` |
| `sepaDonate.buttonBarAmounts` | Comma-separated predefined donation amounts | `10,25,50,100` |
| `sepaDonate.mail.to` | Recipient of donation notifications | `vorstand@musterverein.de` |

The IBAN and recipient are required for generating the EPC GiroCode. If no notification address is configured, no notification email is sent.

## 4. Add the Donation Form

Create a new content element on the page where the donation form should be displayed and select the plugin:

**SEPA Donate — Spendenformular**

The plugin renders the donation form and automatically includes the JavaScript and functional CSS required by the extension.

Visitors can select one of the configured donation amounts or enter an individual amount. They can optionally request a donation receipt and provide the required contact details. After submitting the form, an EPC GiroCode is generated containing the configured account, donation amount and unique payment purpose.

## 5. Notification Emails

When a GiroCode is generated, the extension can send a notification email to the address configured in `sepaDonate.mail.to`.

The notification contains the donation amount and generated payment purpose. If the visitor requested a donation receipt, the entered contact details are included as well.

The notification indicates a **possible donation** only. Generating the QR code does not confirm that the bank transfer was actually executed or received.

Mail delivery uses the TYPO3 mail configuration.

## 6. Templates and Styling

The extension contains the complete functional frontend, but its appearance is intentionally designed to be adapted by the integrating SitePackage.

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

### Template contract

CSS classes and HTML IDs are not part of the JavaScript API and can be changed freely when overriding the template.

The `data-sepa-*` attributes are the stable hooks used by the extension JavaScript and must be preserved. The field names used for submitted data, especially `address[...]`, `formToken` and `company`, must also remain unchanged.

This separation allows the HTML structure and visual styling to be adapted extensively without coupling the JavaScript behavior to project-specific CSS classes.

The CSS shipped by the extension contains only the functional behavior required by the form. Project-specific styling should normally be provided by the SitePackage.

## 7. Spam Prevention

The public QR-code endpoint includes several lightweight protection mechanisms without requiring a Captcha or an external service:

- a hidden trap field for generic form bots,
- one request per TYPO3 site and IP address within 60 seconds,
- a cryptographically random form token generated when the form is rendered,
- a minimum token age of 2 seconds,
- a token lifetime of 30 minutes,
- single-use tokens that are removed after successful processing,
- same-origin validation when the browser sends an `Origin` header.

These measures are intended to reduce automated endpoint and notification-mail abuse while keeping the donation flow frictionless for visitors.

## 8. How It Works

The frontend sends the entered donation data to the extension endpoint. The endpoint path is derived from the current TYPO3 Site Base, so installations using a subdirectory are supported as well.

TYPO3 resolves the current site and the extension reads the SEPA configuration from its Site Settings. The extension then:

1. validates the request and spam-prevention information,
2. generates a unique donation payment purpose,
3. creates an EPC GiroCode containing the configured account, amount and unstructured payment purpose,
4. optionally sends a notification email,
5. returns the QR code and payment information to the frontend.

The generated payment purpose is stored in the EPC GiroCode as unstructured remittance information. It is not an ISO 11649 structured creditor reference.

The actual payment is performed independently in the donor's banking application. The extension does not connect to a bank and does not verify incoming payments.

## License

GPL-2.0-or-later
