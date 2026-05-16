// Funzione utility per aggiungere/rimuovere la classe di errore a un campo del form
function setFieldError(field, hasError) {
    if (!field) return;

    field.classList.toggle('input-error', hasError);
    field.classList.toggle('is-invalid', hasError);
}

const MAX_LISTING_PRICE = 99999999.99;

const ALLOWED_IMAGE_TYPES = [
    'image/png',
    'image/jpeg',
    'image/gif',
    'image/webp'
];

// Funzione per validare il form di registrazione
function validateRegistrationForm() {
    const form = document.forms['registrationForm'];

    if (!form) {
        return true;
    }

    const email = form.inputEmail;
    const password = form.inputPassword;
    const username = form.inputUsername;

    let valid = true;

    if (!email.value.includes('@')) {
        setFieldError(email, true);
        valid = false;
    } else {
        setFieldError(email, false);
    }

    if (password.value.length < 8) {
        setFieldError(password, true);
        valid = false;
    } else {
        setFieldError(password, false);
    }

    if (username.value.trim().length < 3) {
        setFieldError(username, true);
        valid = false;
    } else {
        setFieldError(username, false);
    }

    return valid;
}

// Funzione per validare il form dell'annuncio
function validateListingForm() {
    const form = document.forms['listingForm'];

    if (!form) {
        return true;
    }

    const price = Number(form.price.value);
    const priceError = document.getElementById('priceError');

    if (Number.isNaN(price) || price <= 0 || price > MAX_LISTING_PRICE) {
        setFieldError(form.price, true);

        if (priceError) {
            priceError.classList.remove('d-none');
        }

        return false;
    }

    setFieldError(form.price, false);

    if (priceError) {
        priceError.classList.add('d-none');
    }

    return validateSelectedImage();
}

// Funzione per validare il form della carta
function validateCardForm() {
    const form = document.forms['cardForm'];

    if (!form) {
        return true;
    }

    const name = form.inputName.value.trim();
    const edition = form.inputEdition.value.trim();

    let valid = true;

    if (name.length < 2) {
        setFieldError(form.inputName, true);
        valid = false;
    } else {
        setFieldError(form.inputName, false);
    }

    if (edition.length < 2) {
        setFieldError(form.inputEdition, true);
        valid = false;
    } else {
        setFieldError(form.inputEdition, false);
    }

    return valid;
}

function validateSelectedImage() {
    const imageInput = document.getElementById('cardImage');
    const imageError = document.getElementById('imageError');

    if (!imageInput || imageInput.files.length === 0) {
        hideImageError();
        return true;
    }

    const file = imageInput.files[0];

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        showImageError('Il file selezionato non è un’immagine valida.');
        clearImagePreview();
        return false;
    }

    hideImageError();
    return true;
}

function setupImagePreview() {
    const imageInput = document.getElementById('cardImage');
    const previewBox = document.getElementById('imagePreviewBox');
    const previewImage = document.getElementById('cardImagePreview');
    const removeButton = document.getElementById('removeImagePreview');

    if (!imageInput || !previewBox || !previewImage) {
        return;
    }

    imageInput.addEventListener('change', function () {
        if (imageInput.files.length === 0) {
            clearImagePreview();
            return;
        }

        const file = imageInput.files[0];

        if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
            showImageError('Il file selezionato non è un’immagine valida.');
            clearImagePreview();
            return;
        }

        hideImageError();

        const reader = new FileReader();

        reader.onload = function (event) {
            previewImage.src = event.target.result;
            previewBox.classList.remove('d-none');
        };

        reader.onerror = function () {
            showImageError('Errore durante il caricamento dell’anteprima.');
            clearImagePreview();
        };

        reader.readAsDataURL(file);
    });

    if (removeButton) {
        removeButton.addEventListener('click', function () {
            imageInput.value = '';
            clearImagePreview();
            hideImageError();
        });
    }
}

function clearImagePreview() {
    const previewBox = document.getElementById('imagePreviewBox');
    const previewImage = document.getElementById('cardImagePreview');

    if (previewImage) {
        previewImage.src = '';
    }

    if (previewBox) {
        previewBox.classList.add('d-none');
    }
}

function showImageError(message) {
    const imageInput = document.getElementById('cardImage');
    const imageError = document.getElementById('imageError');

    if (imageInput) {
        setFieldError(imageInput, true);
    }

    if (imageError) {
        imageError.textContent = message;
        imageError.classList.remove('d-none');
    }
}

function hideImageError() {
    const imageInput = document.getElementById('cardImage');
    const imageError = document.getElementById('imageError');

    if (imageInput) {
        setFieldError(imageInput, false);
    }

    if (imageError) {
        imageError.classList.add('d-none');
    }
}

// Attendi il caricamento completo del DOM prima di aggiungere gli event listener
document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.forms['registrationForm'];

    if (registrationForm) {
        registrationForm.addEventListener('submit', (e) => {
            if (!validateRegistrationForm()) {
                e.preventDefault();
            }
        });
    }

    const listingForm = document.forms['listingForm'];

    if (listingForm) {
        setupImagePreview();

        listingForm.addEventListener('submit', (e) => {
            if (!validateListingForm()) {
                e.preventDefault();
            }
        });
    }

    const cardForm = document.forms['cardForm'];

    if (cardForm) {
        cardForm.addEventListener('submit', (e) => {
            if (!validateCardForm()) {
                e.preventDefault();
            }
        });
    }
});