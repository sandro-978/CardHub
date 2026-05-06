// Funzione utility per aggiungere/rimuovere la classe di errore a un campo del form
function setFieldError(field, hasError) {
    // Se il campo non esiste, esci dalla funzione
    if (!field) return;
    // Aggiungi o rimuovi la classe 'input-error' in base al parametro hasError
    field.classList.toggle('input-error', hasError);
}

// Funzione per validare il form di registrazione
function validateRegistrationForm() {
    // Seleziona il form di registrazione
    const form = document.forms['registrationForm'];
    // Ottieni i campi email, password e username dal form
    const email = form.inputEmail;
    const password = form.inputPassword;
    const username = form.inputUsername;

    // Inizializza la variabile di validità
    let valid = true;

    // Valida l'email: deve contenere '@'
    if (!email.value.includes('@')) {
        setFieldError(email, true);
        valid = false;
    } else {
        setFieldError(email, false);
    }

    // Valida la password: deve avere almeno 8 caratteri
    if (password.value.length < 8) {
        setFieldError(password, true);
        valid = false;
    } else {
        setFieldError(password, false);
    }

    // Valida l'username: deve avere almeno 3 caratteri (dopo rimozione spazi)
    if (username.value.trim().length < 3) {
        setFieldError(username, true);
        valid = false;
    } else {
        setFieldError(username, false);
    }

    // Ritorna true se tutti i campi sono validi, false altrimenti
    return valid;
}

// Funzione per validare il form dell'annuncio
function validateListingForm() {
    // Seleziona il form dell'annuncio
    const form = document.forms['listingForm'];
    // Converte il valore del prezzo a numero
    const price = Number(form.price.value);

    // Valida il prezzo: deve essere un numero valido e maggiore di 0
    if (Number.isNaN(price) || price <= 0) {
        setFieldError(form.price, true);
        return false;
    }

    // Se la validazione passa, rimuovi la classe di errore
    setFieldError(form.price, false);
    return true;
}

// Funzione per validare il form della carta
function validateCardForm() {
    // Seleziona il form della carta
    const form = document.forms['cardForm'];
    // Ottieni il nome della carta (trim per rimuovere spazi)
    const name = form.inputName.value.trim();
    // Ottieni l'edizione della carta (trim per rimuovere spazi)
    const edition = form.inputEdition.value.trim();
    // Inizializza la variabile di validità
    let valid = true;

    // Valida il nome: deve avere almeno 2 caratteri
    if (name.length < 2) {
        setFieldError(form.inputName, true);
        valid = false;
    } else {
        setFieldError(form.inputName, false);
    }

    // Valida l'edizione: deve avere almeno 2 caratteri
    if (edition.length < 2) {
        setFieldError(form.inputEdition, true);
        valid = false;
    } else {
        setFieldError(form.inputEdition, false);
    }

    // Ritorna true se tutti i campi sono validi, false altrimenti
    return valid;
}


// Attendi il caricamento completo del DOM prima di aggiungere gli event listener
document.addEventListener('DOMContentLoaded', () => {
    // Seleziona il form di registrazione
    const registrationForm = document.forms['registrationForm'];
    // Se il form esiste, aggiungi il listener al submit
    if (registrationForm) {
        registrationForm.addEventListener('submit', (e) => {
            // Se la validazione fallisce, previeni l'invio del form
            if (!validateRegistrationForm()) {
                e.preventDefault();
            }
        });
    }

    // Seleziona il form dell'annuncio
    const listingForm = document.forms['listingForm']; 
    if (listingForm) {
        // Aggiungi il listener al submit del form annuncio
        listingForm.addEventListener('submit', (e) => {
            // Se la validazione fallisce, previeni l'invio del form
            if (!validateListingForm()) {
                e.preventDefault();
            }
        });
    }

    // Seleziona il form della carta
    const cardForm = document.forms['cardForm']; 
    if (cardForm) {
        // Aggiungi il listener al submit del form carta
        cardForm.addEventListener('submit', (e) => {
            // Se la validazione fallisce, previeni l'invio del form
            if (!validateCardForm()) {
                e.preventDefault();
            }
        });
    }
});


