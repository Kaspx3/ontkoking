-- OPTIONEEL (voer uit na 01 en 02): zes voorbeeldrecepten. Doe dit pas NADAT je een account hebt gemaakt
-- en database_update.sql hebt uitgevoerd. De recepten komen op naam van het eerste account.

INSERT INTO `recepten` (`titel`,`beschrijving`,`bereidingswijze`,`bereidingstijd_min`,`gebruiker_id`,`categorie_id`,`vegetarisch`,`glutenvrij`) VALUES
('Pasta pomodoro','Klassieke pasta met verse tomatensaus.',CONCAT('Kook de pasta volgens de verpakking.\n','Fruit de knoflook in olijfolie.\n','Voeg de tomaten toe en laat 15 minuten sudderen.\n','Meng de saus door de pasta.'),25,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Hoofdgerecht'),1,0),
('Havermoutpannenkoekjes','Zacht en gezond ontbijt.',CONCAT('Pureer haver, banaan en ei tot een beslag.\n','Bak kleine pannenkoekjes in een beetje boter.'),15,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Ontbijt'),1,0),
('Appeltaart','Huisgemaakte appeltaart met kaneel.',CONCAT('Maak een deeg van bloem, boter en suiker.\n','Bekleed de vorm en vul met appel en kaneel.\n','Bak 60 minuten op 180 graden.'),90,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Dessert'),1,0),
('Groentecurry','Romige curry met seizoensgroenten.',CONCAT('Fruit de ui met currypasta.\n','Voeg groenten en kokosmelk toe.\n','Laat 20 minuten zachtjes koken en serveer met rijst.'),35,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Hoofdgerecht'),1,1),
('Avocadotoast','Snel en simpel.',CONCAT('Rooster het brood.\n','Prak de avocado met citroensap en zout.\n','Smeer op het brood.'),10,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Ontbijt'),1,0),
('Chocolademousse','Luchtig toetje van drie ingrediënten.',CONCAT('Smelt de chocolade en laat iets afkoelen.\n','Klop de room stijf en spatel door de chocolade.\n','Zet 2 uur in de koelkast.'),20,(SELECT MIN(id) FROM gebruikers),(SELECT id FROM categorieen WHERE naam='Dessert'),1,1);

INSERT IGNORE INTO `ingredienten` (`naam`) VALUES ('pasta'),('tomaten'),('knoflook'),('olijfolie'),('havermout'),('banaan'),('ei'),('bloem'),('boter'),('suiker'),('appels'),('kaneel'),('ui'),('currypasta'),('kokosmelk'),('gemengde groenten'),('brood'),('avocado'),('pure chocolade'),('slagroom');

INSERT INTO `recept_ingredienten` (`recept_id`,`ingredient_id`,`hoeveelheid`,`eenheid`)
SELECT r.id, i.id, x.h, x.e FROM (
  SELECT 'Pasta pomodoro' t,'pasta' n,250 h,'gram' e UNION ALL
  SELECT 'Pasta pomodoro','tomaten',500,'gram' UNION ALL
  SELECT 'Pasta pomodoro','knoflook',2,'teen' UNION ALL
  SELECT 'Pasta pomodoro','olijfolie',2,'el' UNION ALL
  SELECT 'Havermoutpannenkoekjes','havermout',100,'gram' UNION ALL
  SELECT 'Havermoutpannenkoekjes','banaan',1,'stuks' UNION ALL
  SELECT 'Havermoutpannenkoekjes','ei',2,'stuks' UNION ALL
  SELECT 'Appeltaart','bloem',250,'gram' UNION ALL
  SELECT 'Appeltaart','boter',125,'gram' UNION ALL
  SELECT 'Appeltaart','suiker',100,'gram' UNION ALL
  SELECT 'Appeltaart','appels',1000,'gram' UNION ALL
  SELECT 'Appeltaart','kaneel',2,'tl' UNION ALL
  SELECT 'Groentecurry','ui',1,'stuks' UNION ALL
  SELECT 'Groentecurry','currypasta',2,'el' UNION ALL
  SELECT 'Groentecurry','kokosmelk',400,'ml' UNION ALL
  SELECT 'Groentecurry','gemengde groenten',500,'gram' UNION ALL
  SELECT 'Avocadotoast','brood',2,'plakken' UNION ALL
  SELECT 'Avocadotoast','avocado',1,'stuks' UNION ALL
  SELECT 'Chocolademousse','pure chocolade',200,'gram' UNION ALL
  SELECT 'Chocolademousse','slagroom',300,'ml'
) x
JOIN recepten r ON r.titel = x.t
JOIN ingredienten i ON i.naam = x.n;
