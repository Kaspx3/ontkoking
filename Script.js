// =========================================================
// MijnRecepten – regels toevoegen en verwijderen
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