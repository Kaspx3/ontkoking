# ontkoking
Beroeps leerj2 periode1 – MijnRecepten: een receptensite met favorieten en boodschappenlijst.

## Mappen en bestanden

```
index.php               Startpagina: alle recepten, filters, zoeken, sorteren
recept.php              Eén recept (ingrediënten, bereiding, favoriet, boodschappenlijst)
recept_form.php         Recept toevoegen én bewerken (?id=3)
favorieten.php          Favoriete recepten van de ingelogde gebruiker
favoriet.php            Zet een favoriet aan/uit (geen pagina, alleen een actie)
boodschappenlijst.php   Boodschappenlijst: afvinken, verwijderen, leegmaken
login.php / register.php / logout.php

db.php                  Databaseverbinding + sessie (hier staan de inloggegevens)
includes/
  functions.php         Hulpfuncties: e(), require_login(), ingrediënten, foto's
  header.php            Kop van elke pagina
  footer.php            Voet van elke pagina
  card.php              Eén receptkaart
assets/
  css/style.css         Alle styling, opgedeeld in genummerde onderdelen
  js/recept-form.js     Regels toevoegen/verwijderen + foto-preview
database/               SQL-bestanden, in deze volgorde uitvoeren (phpMyAdmin → SQL)
  01_ontkoking.sql        basistabellen
  02_update.sql           favorieten, boodschappenlijst, filters, categorieën
  03_voorbeeldrecepten.sql  optioneel, ná het maken van een account
uploads/                Geüploade foto's (wordt automatisch gevuld, staat niet in git)
```

## Installeren
1. Voer `database/01_ontkoking.sql`, daarna `database/02_update.sql` uit.
2. Zet alle bestanden op de server, met dezelfde mappenstructuur.
3. Controleer in `db.php` de databasegegevens.
4. Maak een account via `register.php`; voer daarna eventueel `03_voorbeeldrecepten.sql` uit.
5. Zet `$debug` in `db.php` op `false` als alles werkt.

## Afspraken
- Elke pagina begint met `require_once 'db.php';` en eindigt met `include 'includes/footer.php';`.
- Gebruik `e()` voor alles wat je uit de database in HTML print.
- Gebruik altijd `?`-plaatsaanduidingen in SQL, nooit variabelen direct in de query.
- Nieuwe hulpfuncties horen in `includes/functions.php`, nieuwe styling in een bestaand onderdeel van `style.css`.
