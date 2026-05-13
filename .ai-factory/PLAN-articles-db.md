# Plan: articles.db pre blog modul

## Problem
Blog modul by mal mat vlastnu SQLite databázu (articles.db) oddelenu od hlavnej databázy.

## Current State
- Blog modul existuje s ArticleController, ArticleRepository, ArticleService
- Migrácie existujú v `modules/blog/database/migrations/`
- Databáza articles.db je vytvorená (prázdna)

## Issues
- Predchádzajúci pokus zlyhal - ArticleConnection neimplementoval všetky metódy z ConnectionInterface
- PDOSqliteConnection neexistuje v projekte

## Solution

### Task 1: Vytvoriť správnu database connection pre blog
- Použiť existujúci ConnectionInterface z packages/database
- Implementovať všetky požadované metódy
- Alebo použiť iný prístup - konfiguračný súbor

### Task 2: Aktualizovať blog modul
- Upraviť module.php aby používal správnu databázu
- Alebo nechať na global connection a len vytvoriť articles.db s tabuľkami

### Task 3: Spustiť migrácie na articles.db
- Skontrolovať či migrácie fungujú
- Vytvoriť tabuľky v articles.db

### Task 4: Otestovať
- Spustiť blog a overiť že funguje

## Poznámky
- Možno bude potrebné upraviť migrate command aby podporoval rôzne databázy
- Alebo spustiť migrácie manuálne s php