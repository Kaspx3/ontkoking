// =========================================================
// MijnRecepten – receptformulier (recept_form.php):
// regels toevoegen/verwijderen en foto-preview
// =========================================================

// 1. CONFIGURATIE
// Per lijst: de name (voor PHP later), het label (voor toegankelijkheid)
// en een placeholder zodat de gebruiker weet wat hij moet invullen.
const LIJSTEN = {
    ingredienten: {
        naam: 'ingredienten[]',
        label: 'Ingrediënt',
        placeholder: 'Bijv. 200 g bloem',
    },
    stappen: {
        naam: 'stappen[]',
        label: 'Stap',
        placeholder: 'Beschrijf de stap…',
    },
};

// 2. EEN NIEUWE REGEL MAKEN
// We bouwen de <li> met createElement. Dat werkt ook als de lijst leeg is
// (klonen van een bestaande regel zou dan niet kunnen).
function maakRegel(soort) {
    const { naam, placeholder } = LIJSTEN[soort];

    const li = document.createElement('li');
    li.className = 'row';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = naam;               // 'ingredienten[]' → PHP krijgt een array
    input.placeholder = placeholder;

    const knop = document.createElement('button');
    knop.type = 'button';            // voorkomt dat het formulier wordt verstuurd
    knop.className = 'remove';
    knop.textContent = '×';

    li.append(input, knop);
    return li;
}

// 3. NUMMERS BIJWERKEN
// De zichtbare stapnummers doet CSS (counter). Maar de aria-labels
// ("Stap 1", "Stap 2") moeten we zelf bijhouden voor schermlezers.
function nummerOpnieuw(lijst) {
    const { label } = LIJSTEN[lijst.dataset.lijst];

    lijst.querySelectorAll('.row').forEach((rij, i) => {
        rij.querySelector('input').setAttribute('aria-label', `${label} ${i + 1}`);
        rij.querySelector('.remove').setAttribute('aria-label', `${label} ${i + 1} verwijderen`);
    });
}

// 4. EVENT DELEGATION
// Eén luisteraar op het hele document in plaats van op elke knop.
// Zo werken ook knoppen die we later via JavaScript toevoegen.
document.addEventListener('click', (e) => {

    // --- Toevoegen: "+ ingrediënt" / "+ stap" ---
    const addKnop = e.target.closest('.add-btn');
    if (addKnop) {
        const soort = addKnop.dataset.lijst;
        const lijst = document.querySelector(`.rows[data-lijst="${soort}"]`);

        const rij = maakRegel(soort);
        lijst.append(rij);
        nummerOpnieuw(lijst);
        rij.querySelector('input').focus();   // cursor staat direct in het nieuwe veld
        return;
    }

    // --- Verwijderen: de × knop ---
    const verwijderKnop = e.target.closest('.remove');
    if (verwijderKnop) {
        const rij = verwijderKnop.closest('.row');
        const lijst = rij.parentElement;

        rij.remove();
        nummerOpnieuw(lijst);
    }
});

// =========================================================
// 5. FOTO-PREVIEW
// =========================================================
const fotoInput   = document.getElementById('foto');
const fotoPreview = document.getElementById('fotoPreview');
const uploadVlak  = document.getElementById('uploadVlak');
const origineel   = fotoPreview.dataset.origineel || null;   // bestaande foto (alleen bij bewerken)
let previewUrl = null;

// Terug naar het begin: de bestaande foto, of het lege uploadvlak
function resetFoto() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);   // geheugen vrijmaken
    previewUrl = null;

    if (origineel) {
        fotoPreview.src = origineel;
        fotoPreview.hidden = false;
        uploadVlak.classList.add('is-filled');
    } else {
        fotoPreview.hidden = true;
        fotoPreview.removeAttribute('src');
        uploadVlak.classList.remove('is-filled');
    }
}

fotoInput.addEventListener('change', () => {
    const bestand = fotoInput.files[0];
    resetFoto();
    if (!bestand) return;                              // bijv. op "Annuleren" gedrukt in het venster

    // Maximale grootte komt van PHP (data-max-bytes) zodat browser en server hetzelfde zeggen
    const maxBytes = Number(fotoInput.dataset.maxBytes) || 2 * 1024 * 1024;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(bestand.type)) {
        alert('Kies een jpg-, png- of webp-foto.');
        fotoInput.value = '';
        return;
    }
    if (bestand.size > maxBytes) {
        alert(`De foto is te groot. Maximaal ${Math.floor(maxBytes / 1048576)} MB.`);
        fotoInput.value = '';
        return;
    }

    previewUrl = URL.createObjectURL(bestand);
    fotoPreview.src = previewUrl;
    fotoPreview.hidden = false;
    uploadVlak.classList.add('is-filled');
});
