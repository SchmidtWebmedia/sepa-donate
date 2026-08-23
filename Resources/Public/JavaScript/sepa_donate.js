(function () {
    'use strict';

    document.querySelectorAll('[data-sepa-donation-form]').forEach(function (donationForm) {
        const amountBtns = donationForm.querySelectorAll('[data-sepa-amount]');
        const amountInput = donationForm.querySelector('[data-sepa-custom-amount]');
        const submitBtn = donationForm.querySelector('[data-sepa-submit]');
        const result = donationForm.querySelector('[data-sepa-result]');
        const qrImg = donationForm.querySelector('[data-sepa-qr-code]');
        const purposeEl = donationForm.querySelector('[data-sepa-result-purpose]');
        const amountEl = donationForm.querySelector('[data-sepa-result-amount]');
        const confirmationEl = donationForm.querySelector('[data-sepa-confirmation]');
        const donationReceiptCheck = donationForm.querySelector('[data-sepa-receipt-toggle]');
        const addressBlock = donationForm.querySelector('[data-sepa-receipt-address]');
        const endpoint = donationForm.dataset.sepaEndpoint;

        if (!amountInput || !submitBtn || !result || !qrImg || !purposeEl || !amountEl || !confirmationEl || !endpoint) {
            console.error('SEPA Donate: required template hooks are missing.');
            return;
        }

        let currentAmount = 0;

        function setAmount(amount) {
            currentAmount = amount;
            submitBtn.disabled = currentAmount <= 0;
        }

        function formatAmount(amount) {
            return amount.toLocaleString('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }) + '\u00a0€';
        }

        donationForm.querySelectorAll('[data-sepa-copy]').forEach(function (el) {
            el.setAttribute('role', 'button');
            el.setAttribute('tabindex', '0');

            const copy = async function () {
                const text = el.textContent.trim();
                try {
                    await navigator.clipboard.writeText(text);
                    el.classList.add('is-copied');
                    setTimeout(function () { el.classList.remove('is-copied'); }, 1200);
                } catch (err) {
                    console.error('Kopieren fehlgeschlagen:', err);
                }
            };

            el.addEventListener('click', copy);
            el.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    copy();
                }
            });
        });

        amountBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                amountBtns.forEach(function (button) { button.classList.remove('is-active'); });
                btn.classList.add('is-active');
                amountInput.value = '';
                setAmount(parseFloat(btn.dataset.sepaAmount));
            });
        });

        amountInput.addEventListener('input', function () {
            amountBtns.forEach(function (button) { button.classList.remove('is-active'); });
            setAmount(parseFloat(amountInput.value) || 0);
        });

        if (donationReceiptCheck && addressBlock) {
            donationReceiptCheck.addEventListener('change', function () {
                addressBlock.classList.toggle('is-visible', donationReceiptCheck.checked);
                addressBlock.setAttribute('aria-hidden', String(!donationReceiptCheck.checked));
            });
        }

        submitBtn.addEventListener('click', function () {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');

            const formData = new FormData();
            formData.append('amount', String(currentAmount));
            formData.append('website', donationForm.querySelector('[data-sepa-honeypot]')?.value || '');

            if (donationReceiptCheck?.checked) {
                formData.append('withReceipt', '1');
                donationForm.querySelectorAll('[name^="address["]').forEach(function (input) {
                    formData.append(input.name, input.value);
                });
            }

            fetch(endpoint, {
                method: 'POST',
                body: formData,
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Netzwerkfehler');
                    }
                    return response.json();
                })
                .then(function (data) {
                    qrImg.src = 'data:image/svg+xml;base64,' + data.qrCode;
                    purposeEl.textContent = data.purpose;
                    amountEl.textContent = formatAmount(data.amount);
                    confirmationEl.textContent = donationReceiptCheck?.checked
                        ? 'Ihre Überweisung wurde vorbereitet und die Daten für eine mögliche Spendenquittung wurden an den Verein übermittelt.'
                        : 'Ihre Überweisung wurde vorbereitet.';

                    result.classList.add('is-visible');
                    result.setAttribute('aria-hidden', 'false');
                    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(function (error) {
                    console.error(error);
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
                });
        });
    });
}());
