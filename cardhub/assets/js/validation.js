function setFieldError(field, hasError) {
    if (!field) return;
    field.classList.toggle('input-error', hasError);
}

function validateRegistrationForm() {
    const form = document.forms['registrationForm'];
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

function validateListingForm() {
    const form = document.forms['listingForm'];
    const price = Number(form.price.value);

    if (Number.isNaN(price) || price <= 0) {
        setFieldError(form.price, true);
        return false;
    }

    setFieldError(form.price, false);
    return true;
}
