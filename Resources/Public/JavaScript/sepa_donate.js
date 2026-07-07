(function () {
    'use strict';

    const donationForm = document.querySelector('.donation-form');
    if(!donationForm) return;

    const amountBtns = donationForm.querySelectorAll('.donation-amount__btn');
    const amountInput   = donationForm.querySelector('.donation-amount__input');
    const submitBtn     = donationForm.querySelector('.donation-submit__btn');
    const result      = donationForm.querySelector('.donation-result');
    const qrImg         = donationForm.querySelector('.donation-result__qr-img');
    const referenceEl    = donationForm.querySelector('#donation-result-reference');
    const amountEl      = donationForm.querySelector('#donation-result-amount');
    const confirmationEl = donationForm.querySelector('#donation-confirmation');

    const donationReceiptCheck = donationForm.querySelector('#donation-receipt-check');
    const addressBlock = donationForm.querySelector('#donation-address');

    let currentAmount = 0;

    function setAmount(amount) {
        currentAmount = amount;
        updateSubmitBtn();
    }

    function updateSubmitBtn() {
        submitBtn.disabled = currentAmount <= 0;
    }

    function formatAmount(amount) {
        return amount.toLocaleString('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + '\u00a0€';
    }

    document.querySelectorAll('.donation-result__value--copyable').forEach((el) => {
        el.setAttribute('role', 'button');
        el.setAttribute('tabindex', '0');

        const copy = async () => {
            const text = el.textContent.trim();
            try {
                await navigator.clipboard.writeText(text);
                el.classList.add('is-copied');
                setTimeout(() => el.classList.remove('is-copied'), 1200);
            } catch (err) {
                console.error('Kopieren fehlgeschlagen:', err);
            }
        };

        el.addEventListener('click', copy);
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                copy();
            }
        });
    });

    amountBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            amountBtns.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            amountInput.value = '';
            setAmount(parseFloat(btn.dataset.amount));
        });
    });

    amountInput.addEventListener('input', function () {
        amountBtns.forEach(function (b) { b.classList.remove('is-active'); });
        setAmount(parseFloat(amountInput.value) || 0);
    });

    donationReceiptCheck.addEventListener('change', function () {
        addressBlock.classList.toggle('is-visible', donationReceiptCheck.checked);
        addressBlock.setAttribute('aria-hidden', String(!donationReceiptCheck.checked));
    });

    submitBtn.addEventListener('click', function () {
        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');

        const formData = new FormData();
        formData.append('amount', String(currentAmount));

        if (donationReceiptCheck.checked) {
            formData.append('withReceipt', '1');
            donationForm.querySelectorAll('.donation-address__input').forEach(function (input) {
                if (input.name) {
                    formData.append(input.name, input.value);
                }
            });
        }

        const actionUrl = '/api/sepa-donate/qr-code';

        fetch(actionUrl, {
            method: 'POST',
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Netzwerkfehler');
                }
                return response.json();
            })
            .then( (data) => {
                qrImg.src = 'data:image/svg+xml;base64,' + data.qrCode;
                referenceEl.textContent = data.reference;
                amountEl.textContent = formatAmount(data.amount);
                confirmationEl.textContent = donationReceiptCheck.checked
                    ? 'Eine Benachrichtigung mit Ihren Quittungsdaten wurde an den Verein gesendet.'
                    : 'Eine Benachrichtigung wurde an den Verein gesendet.';

                result.classList.add('is-visible');
                result.setAttribute('aria-hidden', 'false');
                result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch( (error) => {
                console.error(error);
                submitBtn.disabled = false;
                submitBtn.classList.remove('is-loading');
                alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
            });
    });

}());
