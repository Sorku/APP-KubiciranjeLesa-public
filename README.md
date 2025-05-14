# APP-Kubiciranje

Aplikacija za izračun kubature lesa z možnostjo ustvarjanja računov in vodenja evidence izračunov.

## Nastavitev okolja

Ta projekt uporablja okoljske spremenljivke za shranjevanje občutljivih informacij. Sledite tem korakom za nastavitev:

1. Kopirajte `.env.example` v novo datoteko z imenom `.env`
2. V datoteki `.env` nastavite prave podatke za povezavo z vašo bazo
3. Poskrbite, da datoteke `.env` ne boste poslali v repozitorij - mora biti izključena preko `.gitignore`

```bash
cp .env.example .env
# Nato uredite .env z vašimi podatki
```

## Funkcionalnosti

- Prijava in registracija uporabnikov
- Ustvarjanje računov za različne stranke
- Izračun kubature lesa glede na premer, dolžino in vrsto lesa
- Avtomatski izračun volumna brez lubja
- "Remember me" funkcionalnost za enostavnejšo prijavo
- Sortiranje tabel za lažji pregled podatkov

## Namestitev

1. Klonirajte repozitorij ali prenesite datoteke na vaš strežnik
2. Ustvarite MySQL bazo podatkov
3. Uvozite strukturo baze iz datoteke `database/database.sql`
4. Nastavite podatke za povezavo z bazo v datoteki `strani/dbh.stran.php`
5. Dostopajte do aplikacije prek spletnega brskalnika

## Tehnične zahteve

- PHP 7.0 ali novejši
- MySQL strežnik
- Spletni strežnik (Apache, Nginx, itd.)

## Uporaba

1. Registrirajte se ali se prijavite v aplikacijo
2. Ustvarite nov račun s klikom na gumb "Nov Račun"
3. Vnesite mere lesa (dolžino, premer) in količino
4. Izberite vrsto lesa za pravilen izračun
5. Aplikacija bo avtomatsko izračunala kubaturo

## Vzdrževanje

- Redno varnostno kopirajte podatkovno bazo
- Posodabljajte PHP in MySQL na najnovejše različice