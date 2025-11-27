CHANGELOG – SEUP (Sustav Elektroničkog Uredskog Poslovanja)
1.0.0 – Initial Release

Prva funkcionalna verzija SEUP modula.

Osnovna struktura modula generirana putem Dolibarr ModuleBuilder-a.

Dodani početni modeli za Predmete, Akte i Priloge.

Postavljeni temeljni SQL predlošci i osnovna navigacija.

Hardkodirani testni sadržaji za interne potrebe razvoja.

2.0.0 – Core Stabilizacija

Potpuna reorganizacija direktorija (class/, pages/, lib/, sql/, langs/ itd.).

Implementirani modeli:

Predmet

Akt_helper

Prilog_helper

Suradnici_helper

Sortiranje_helper

Dodan osnovni workflow za kreiranje, prikaz i uređivanje predmeta.

Dodani backend alati za sortiranje, pretragu i filtriranje.

Počeci Nextcloud integracije – priprema API klase.

Prvi draft OnlyOffice integracije (bez potpune implementacije).

Dodan sustav tagova i osnovne administracijske stranice.

2.5.0 – DMS Ekspanzija

Uvedena napredna podrška za rad s prilozima i dokumentima.

Dovršena Nextcloud API integracija: kreiranje foldera, upload, strukture.

Nadograđen interface za rad s aktima, povezivanje akata na predmete.

Uvedeni helperi za generiranje dokumenata (PDF, DOCX).

Dodane interne klase za digitalni potpis i provjeru potpisa.

Dodan "Plan klasifikacijskih oznaka".

Prvi stabilni importer podataka.

3.0.0 – „Production Ready“ Refactor

Veliko čišćenje i refaktor kodne baze.

Uklanjanje starih placeholder datoteka i nepotrebnih skeleton fajlova.

Usklađivanje strukture s Dolibarr 22 standardima.

Optimiziran rad s bazom: novi SQL predlošci, bolja organizacija tablica.

Uređivanje svih stranica (pages/) – UX poboljšanja, layout stabilizacija.

Ujednačavanje PHP klasa i naming conventiona.

Uvedene dodatne funkcije za korisničke uloge i interne workflowe.

Dodano više sigurnosnih provjera i sanitizacije inputa.

Značajno brže učitavanje većih listi predmeta i akata.

3.0.1 – Licensing & Packaging Cleanup

Uklonjene sve GPL datoteke i naslijeđeni ModuleBuilder headeri.

Dodan novi proprietary LICENSE.md (8Core).

Kreiran novi info.xml kompatibilan s Dolibarr 22.

Usklađeni brojevi verzija i modul identificatori.

Čišćenje vendor-a: uklanjanje duplih JWT implementacija.

Priprema za stabilno izdanje i distribuciju prema klijentima.

Dokumentacija ažurirana: README, struktura, changelog.

---

## 3.1.0 – Zaprimanja i Otprema Fundamentals

**Datum:** Q1 2024

### Nove značajke
- ✉️ Dodan modul za zaprimanje pošte i dokumentacije
- 📤 Implementirana baza otpreme (`llx_a_otprema` tablica)
- 🔄 Osnovni workflow za registraciju primljene i poslane pošte
- 🔗 Povezivanje zaprimanja/otprema s predmetima

### Tehničke izmjene
- SQL migracije za nove tablice
- Backend struktura za evidentiranje ulazne/izlazne pošte

---

## 3.2.0 – Dizajn Modernizacija

**Datum:** Q1 2024

### UI/UX
- 🎨 Uveden moderan CSS dizajn sustav (`seup-modern.css`)
- 📱 Redizajnirane glavne stranice: predmeti, zaprimanja, otprema
- 📐 Poboljšan responsive layout i mobile experience
- 🧭 Dodan novi header i navigacijski sustav
- ✨ Vizualne optimizacije formi i tablica

---

## 3.3.0 – Zaprimanja Extended

**Datum:** Q2 2024

### Proširenja
- 🔍 Napredne funkcionalnosti za zaprimanja
- 🔎 Pretraga, filtriranje i sortiranje zaprimljenih dokumenata
- 🤖 Automatsko povezivanje zaprimanja s postojećim predmetima
- 📊 Dodani statusni indikatori i workflow kontrole
- 📥 Export funkcionalnosti za zaprimanja

---

## 3.4.0 – Otprema Advanced

**Datum:** Q2 2024

### Proširenja
- 📮 Proširene mogućnosti otpreme dokumenata
- 👥 Dodana integracija s adresarom (suradnici)
- 📍 Praćenje statusa otpreme i potvrde dostave
- 📦 Grupna otprema dokumenata
- 🏷️ Generiranje poštanskih oznaka i potvrda

---

## 3.5.0 – Code Cleanup Phase 1

**Datum:** Q2 2024

### Optimizacije
- ⚡ Refaktorirani helper classes za bolje performance
- 🧹 Uklonjen nekorišteni legacy kod
- 🗄️ Optimizacija SQL upita
- 📝 Standardizacija PHP dokumentacije i komentara
- 🛡️ Poboljšana error handling logika

---

## 3.6.0 – UI/UX Improvements

**Datum:** Q3 2024

### Poboljšanja korisničkog iskustva
- 🎯 Redesign predmet.php stranice
- 🪟 Novi modalni prozori za brže akcije
- 💡 Dodani tooltipovi i inline help
- 🔤 Poboljšan autocomplete za suradnike i oznake
- ⚡ Optimizacija ajax poziva za brže učitavanje

---

## 3.7.0 – Security & Validation

**Datum:** Q3 2024

### Sigurnost
- 🔐 Dodane dodatne sigurnosne provjere
- ✅ Input sanitizacija i validacija na svim formama
- 🛡️ CSRF zaštita na kritičnim akcijama
- 💉 SQL injection prevencija - prepared statements
- 🔑 Session management poboljšanja

---

## 4.0.0 – Major Architecture Update

**Datum:** Q4 2024

### Arhitekturne promjene
- 🏗️ Potpuna reorganizacija class strukture
- 🔧 Uvedeni novi pattern: DataLoader, ActionHandler, ViewHelper
- 📦 Refaktor `predmet.class.php` za modularnost
- 🎯 Bolja separacija logike i prikaza
- 🚀 Performance optimizacije na velikim bazama podataka

---

## 4.1.0 – OMAT Generator

**Datum:** Q4 2024

### Nova funkcionalnost
- 🔢 Implementiran sustav za generiranje OMAT brojeva
- ⚙️ Automatska alokacija brojeva prema pravilima
- 🎛️ Konfigurabilan format brojeva ustanove
- 🔗 Integracija s predmetima i aktima
- ✔️ Provjera duplikata i validacija

---

## 4.2.0 – Document Preview System

**Datum:** Q1 2025

### Nova funkcionalnost
- 👁️ Dodan sustav za pregled dokumenata
- 📄 PDF viewer integracija
- 📝 DOCX pretvorba u PDF za preview
- 🖼️ Thumbnails za brži pregled
- 🖥️ Full-screen mode za dokumente

---

## 4.2.5 – Omot & Stabilizacija (CURRENT)

**Datum:** Q1 2025

### Finalizacija
- 📋 Implementiran sustav omota za predmete
- 🔍 Stranica za predpregled omota prije ispisa
- 🧹 Finalna čišćenja koda i optimizacije
- 🔧 Popravke funkcionalnosti u zaprimanjima i otpremama
- 🐛 Bugfixevi i stability improvements
- 🚀 Priprema za production deployment

---
