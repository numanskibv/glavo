# Glavo

Glavo is een flashcard-gebaseerde taalleerapplicatie voor het leren van O.A. Bulgaars. Studenten oefenen woordenschat via een swipe-game, terwijl docenten cursussen en lessen beheren.

---

## Functies

### Voor studenten
- **Flashcard-game** — een kaart toont een Nederlands woord en een Bulgaarse vertaling. Ongeveer de helft van de vertalingen is een lokmiddel (decoy). De student beoordeelt of het paar klopt met "✓ Goed" of "✕ Fout".
- **Spaced repetition** — verkeerde kaarten komen na een paar beurten terug; goed beantwoorde kaarten stijgen in mastery-niveau.
- **Efficiëntie-ring** — live percentage correct beantwoord, zichtbaar tijdens het oefenen.
- **Sessielog** — overzicht van alle beantwoorde kaarten in de huidige sessie (clipboard-icoon).
- **Voortgang resetten** — per les de mastery en pogingen terugzetten naar nul.
- **XP & streak** — elke correcte kaart levert XP op; dagelijkse oefening bouwt een streak.

### Voor docenten
- **Cursus- en lesbeheer** — maak cursussen en lessen aan, bewerk flashcards.
- **Leereditor** — voeg individuele flashcards toe of importeer ze in bulk via Excel.
- **Teacher Dashboard** — overzicht van alle cursussen en voortgang.

---

## Technische stack

| Laag | Technologie |
|---|---|
| Backend | PHP 8.2 · Laravel 12 |
| Realtime UI | Livewire 4 · Alpine.js |
| Componenten | Flux UI (livewire/flux) |
| Styling | Tailwind CSS v4 · Geist font |
| Auth | Laravel Fortify (incl. 2FA) |
| Database | SQLite (dev) / MySQL (prod) |
| Build | Vite 7 |
| Testing | Pest 4 |

---

## Datamodel

```
Language
  └── Term (woord + definitie + taal)

Course
  └── Lesson
        └── Flashcard  (koppelt Term aan een les, bijhoudt mastery & next_review_at)
              └── FlashcardAttempt  (per gebruiker: correct/incorrect + timestamp)

User  (rol: student / teacher / admin · xp · streak)
```

---

## Installatie

### Vereisten
- PHP >= 8.2
- Composer
- Node.js >= 18
- [Laravel Herd](https://herd.laravel.com/) of een andere lokale server

### Stappen

```bash
# 1. Kloon de repository
git clone <repo-url> glavo
cd glavo

# 2. Installeer PHP-afhankelijkheden
composer install

# 3. Installeer JS-afhankelijkheden
npm install

# 4. Configureer de omgeving
cp .env.example .env
php artisan key:generate

# 5. Draai de migraties (maakt een SQLite-database aan)
php artisan migrate

# 6. (Optioneel) Vul de database met testdata
php artisan db:seed

# 7. Start de assetcompiler
npm run dev
```

Open de app via `http://glavo.test` (Herd) of het adres dat je lokale server aangeeft.

---

## Rollen

| Rol | Rechten |
|---|---|
| `student` | Oefenen, voortgang bekijken |
| `teacher` | Cursussen, lessen en flashcards beheren |
| `admin` | Alles van teacher + gebruikersbeheer |

Een nieuw geregistreerde gebruiker krijgt standaard de rol `student`. Verander de rol via Tinker of rechtstreeks in de database:

```bash
php artisan tinker
>>> App\Models\User::find(1)->update(['role' => 'teacher']);
```

---

## Testen

```bash
php artisan test
# of
./vendor/bin/pest
```

---

## Licentie

MIT
