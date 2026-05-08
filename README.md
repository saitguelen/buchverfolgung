# BuchClub Verfolgung 📚

Eine wöchentliche Zielverfolgungs-Webanwendung für Buchclubs. Komplett in Vanilla PHP geschrieben, mit einem modernen, responsiven Dark-Theme (ohne externe CSS-Frameworks) und PostgreSQL-Datenbank.

## 🚀 Funktionen & Regeln (Schritt für Schritt)

### 1. Registrierung & Anmeldung
- **Regel:** Neue Mitglieder können sich nur mit einem gültigen **Einladungscode** (Invite Code) registrieren. So bleibt der Club exklusiv.
- **Regel:** Passwörter müssen aus Sicherheitsgründen mindestens 6 Zeichen lang sein.
- **Sicherheit (Rate Limiting):** Nach 5 fehlerhaften Anmeldeversuchen wird das Konto für 15 Minuten gesperrt, um Brute-Force-Angriffe zu verhindern.

### 2. Benutzerrollen
- **Admin:** Der erste Benutzer (wird beim ersten Aufruf automatisch erstellt, wenn die Datenbank leer ist) erhält die Admin-Rolle. Der Admin kann:
  - Mitglieder aus dem System löschen.
  - Ziele für andere Mitglieder hinzufügen.
  - Ziele von anderen Mitgliedern löschen.
  - Einen manuellen "Reset" des wöchentlichen Fortschritts durchführen.
  - Einen detaillierten Bericht über alle Nutzer einsehen.
- **Member (Mitglied):** Kann sich einloggen, eigene Ziele setzen und den eigenen Fortschritt aktualisieren.

### 3. Ziele & Fortschritt (Das Herzstück)
- **Ziele setzen:** Jedes Mitglied kann im Bereich *Meine Ziele* (`ziele.php`) individuelle Ziele für die Woche setzen (z.B. "50 Seiten lesen", "2 Stunden Hörbuch").
- **Fortschritt eintragen:** Mitglieder aktualisieren kontinuierlich ihren Fortschritt. Der Fortschrittsbalken füllt sich dynamisch (mit flüssigen CSS-Animationen).
- **Wöchentlicher Reset (Die "Neue Woche"-Regel):**
  - Das System basiert auf Kalenderwochen (Start: Montag).
  - Jeden Montag beginnt eine neue Woche. Der aktuelle Fortschrittswert startet für die neue Woche wieder bei `0`.
  - Die Datenbank speichert die Fortschritte pro Woche (`week_start`), alte Daten gehen also in der Datenbank nicht verloren.
  - Dieser Reset passiert faktisch automatisch durch die Logik in `funktionen.php`, aber es gibt auch ein Cronjob-Skript (`cronjobs/woechentlicher_reset.php`), das montags um 00:00 Uhr aufgerufen werden kann, um die 0-Werte explizit in die Datenbank zu schreiben.

### 4. Das Dashboard (Übersicht & Motivation)
- **Transparenz:** Alle Mitglieder sehen auf dem Dashboard (`dashboard.php`), wie weit die anderen in der aktuellen Woche sind.
- **Champion der Woche:** Wer alle seine Ziele erreicht hat und die beste Abschlussquote besitzt, wird oben prominent als "Champion der Woche" gefeiert.
- **Status-Gruppen:** Nutzer werden automatisch in zwei Listen eingeteilt: "Geschafft!" (Finished) und "Fortlaufend" (Continuing).
- **WhatsApp-Nachrichten Generator:** Mit einem Klick kann eine Textvorlage für die WhatsApp-Gruppe erstellt werden. Es gibt drei Tonalitäten:
  - *Motivierend*
  - *Lustig*
  - *Einfach*
  - Der Text lässt sich mit dem Button direkt in die Zwischenablage kopieren.

### 5. Mehrsprachigkeit
- Das System unterstützt Deutsch (`de`), Türkisch (`tr`) und Englisch (`en`).
- Die Sprache kann auf jeder Seite jederzeit über die Flaggen-Buttons oben rechts gewechselt werden und wird in der Session gespeichert.

---

## 🛠️ Technologie-Stack
- **Backend:** Vanilla PHP 8.2 (Keine Frameworks, pures PHP)
- **Frontend:** HTML5, CSS3 (Custom CSS Grid/Flexbox, Glassmorphism, Dark Theme), Vanilla JavaScript
- **Datenbank:** PostgreSQL (via PDO)
- **Sicherheit:** CSRF-Tokens auf allen Formularen, XSS-Schutz, Session Fixation Schutz, Password Hashing.

## 📁 Projektstruktur
Um die Kompatibilität mit den Github-Anforderungen zu gewährleisten, wurden alle Ordner und Kommentare in Deutsch benannt:

*   **/oeffentlich** (Public / Web Root) - *Beinhaltet alle direkt aufrufbaren Dateien (`index.php`, `login.php`, `dashboard.php` etc.) und das CSS/JS.*
*   **/einbindungen** (Includes) - *Logik, die eingebunden wird (`datenbank.php`, `authentifizierung.php`, `sprache.php`, `funktionen.php`).*
*   **/sprachen** (Lang) - *Die Übersetzungs-Arrays (`de.php`, `tr.php`, `en.php`).*
*   **/cronjobs** (Cron) - *Skripte, die im Hintergrund oder zeitgesteuert ausgeführt werden.*

## 💻 Installation & Ausführung

### Variante 1: Railway / Docker (Empfohlen für Produktion)
Das Projekt enthält ein `Dockerfile` und eine `railway.json` und ist bereit für 1-Klick-Deployments auf Plattformen wie Railway.app.
1. Verbinde das Github Repository mit Railway.
2. Füge die PostgreSQL-Datenbank als Service in Railway hinzu.
3. Setze die Umgebungsvariablen (siehe unten).
4. Deploy!

### Variante 2: Lokal (XAMPP/MAMP)
1. Klone das Repository in deinen `htdocs` oder `www` Ordner.
2. Konfiguriere deinen Apache so, dass der `DocumentRoot` auf den Ordner `/oeffentlich` zeigt. (Alternativ leitet die `.htaccess` Aufrufe weiter).
3. Stelle sicher, dass die PostgreSQL PDO Erweiterung in PHP aktiviert ist.
4. Öffne `einbindungen/datenbank.php` und trage dort deine lokalen Datenbankzugangsdaten im Fallback-Bereich ein.
5. Rufe die Seite im Browser auf. Die Tabellen werden beim ersten Laden automatisch erstellt!

## ⚙️ Umgebungsvariablen (.env / Server-Variablen)
Diese Variablen können in der Produktivumgebung (z.B. Railway) gesetzt werden:
- `DATABASE_URL` : Die Verbindungs-URL zur PostgreSQL-Datenbank (Format: `postgres://user:pass@host:port/dbname`).
- `INVITE_CODE` : Der erforderliche Code für Neuanmeldungen. (Standard-Fallback: `bookclub2026`).
- `ADMIN_PASSWORD` : Das Passwort für den ersten "admin"-Benutzer, der automatisch erstellt wird. (Standard-Fallback: `admin123`).

---
*Viel Spaß beim Lesen und Ziele erreichen!* 📖🚀
