# Plan: Modulárny systém databáz pre Marko Framework

## Cieľ
Každý modul v Marko Framework má vlastnú SQLite databázu a moduly je možné pridávať/odoberať.

## Architecture
Každý modul má vlastnú databázu v `storage/data/<modul>.db`

## Moduly

### 1. modules/database-modular (NOVÝ) - bez vlastnej databázy
- **Úloha:** Spravuje modulové databázy (len číta config, žiadne vlastné tabuľky)
- **Obsahuje:** ModuleDatabaseResolver
- **Databáza:** Žiadna - používa hlavnú database.sqlite alebo len číta konfiguráciu

### 2. modules/blog (existujúci)
- **Databáza:** articles.db

### 3. modules/cardboard (existujúci)
- **Databáza:** cardboard.db

## Tasks

### Task 1: Vytvoriť modules/database-modular ✅
- [x] 1.1 Vytvoriť adresár modules/database-modular
- [x] 1.2 Vytvoriť module.php
- [x] 1.3 Vytvoriť ModuleDatabaseResolverInterface.php
- [x] 1.4 Vytvoriť ModuleDatabaseResolver.php
- [x] 1.5 Vytvoriť composer.json

### Task 2: Aktualizovať config/database.php ✅
- [x] 2.1 Pridať 'modules' konfiguráciu

### Task 3: Vytvoriť articles.db ✅
- [x] 3.1 Vytvoriť prázdnu databázu

### Task 4: Blog modul - použiť vlastnú databázu ⏳
- [ ] 4.1 Upraviť modules/blog/module.php použiť ModuleDatabaseResolver

### Task 5: CLI Commands ⏳
- [ ] 5.1 marko module:add
- [ ] 5.2 marko module:remove

## Success Criteria
- [x] modules/database-modular existuje a funguje
- [ ] Blog používa articles.db
- [ ] Moduly je možné pridávať/odoberať