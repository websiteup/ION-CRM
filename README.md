# ION CRM

Un sistem complet de management pentru relațiile cu clienții (CRM) dezvoltat cu Laravel 11, Bootstrap 5 și Livewire 3. ION CRM oferă o soluție modernă și flexibilă pentru gestionarea clienților, proiectelor, serviciilor și echipelor de lucru.

## 📋 Descriere

ION CRM este o aplicație web modernă proiectată pentru a simplifica gestionarea activităților de business. Sistemul oferă instrumente puternice pentru managementul clienților, proiectelor, task-urilor și colaborării în echipă, într-o interfață intuitivă și responsive.

## 🚀 Tehnologii

- **Backend:** Laravel 11
- **Frontend:** Bootstrap 5, Livewire 3
- **UI Components:** Bootstrap Icons, Summernote WYSIWYG Editor
- **Notifications:** Toastify JS, Email (SMTP), Telegram Bot
- **Database:** MySQL/PostgreSQL/SQLite
- **PHP:** ^8.2

## ✨ Caracteristici Implementate

### 👥 Management Utilizatori
- Sistem complet de gestionare utilizatori cu roluri și permisiuni
- Roluri: Administrator, Manager, Vânzări, Utilizator
- Profil utilizator cu foto, semnătură email HTML, preferințe notificări
- Protecție împotriva ștergerii singurului administrator
- Managementul permisiunilor bazat pe roluri

### 📊 Dashboard
- Statistici generale: total clienți, leads, customers, servicii
- Widget pentru ultimii 5 clienți
- Interfață intuitivă cu sidebar vertical

### 👤 Clienți
- Gestionare completă a clienților (Leads și Customers)
- Câmpuri: Nume, Prenume, Email, Telefon, Țară, Adresă
- Căutare și filtrare
- Paginare

### 💼 Servicii
- Management servicii cu prețuri, taxe și tipuri de unitate
- Upload foto pentru servicii
- Urmărire creator și ultimul updater
- Descriere detaliată

### 🎯 Proiecte
- Management complet de proiecte
- Legătură cu clienți
- Tipuri de facturare: Rată fixă sau Rată orară
- Statusuri: Nu a început, În așteptare, În progres, Finalizat, Anulat
- Date de început și sfârșit
- Acces portal clienți (opțional)
- Membri de proiect
- Metrice de proiect: sume facturate, costuri, ore lucrate

### 📋 Boards Kanban
- Interfață Kanban similară cu Trello
- Coloane personalizabile (creare, editare, ștergere, reordonare)
- Drag & drop pentru task-uri și coloane
- Task-uri cu titlu, descriere HTML, prioritate, utilizator atribuit, dată scadență
- Etichete (labels) predefinite și personalizate
- Acces pentru Administratori și Manageri
- Link public pentru clienți (read-only)
- Management membri board
- Poziționare coloane fixă (272px)

### ✅ Task-uri
- Task-uri cu descriere HTML (Summernote)
- Prioritate, utilizator atribuit, dată scadență
- Etichete multiple
- Drag & drop între coloane
- Sortare automată în coloane

### ⚙️ Setări
- **General:** Nume aplicație, limbă default, fus orar, format dată, logo aplicație
- **Email:** Configurare SMTP completă (Host, Port, Username, Password, Encryption, From Name, From Email)
- **Telegram:** Configurare token bot pentru notificări
- **Companie:** Detalii companie, logo, prefix-uri pentru facturi și proforme
- **Taxe:** Management multiple taxe cu setare default
- **Valute:** Management valute cu rate (EUR, RON, USD)
- **Limbaje:** Suport pentru multiple limbi (English, Romanian, German)
- Clear cache

### 🔔 Notificări
- Notificări Email prin SMTP configurat
- Notificări Telegram prin bot
- Notificări frontend cu Toastify JS
- Preferințe notificări per utilizator (task creat, atribuit, actualizat, deadline)
- Notificări automate pentru evenimente task

### 📱 Interfață
- Sidebar vertical responsive
- Design modern și intuitiv
- Notificări non-blocking cu Toastify
- Editor HTML Summernote pentru conținut rich
- Compatibilitate mobile

## 📦 Instalare

### Cerințe
- PHP ^8.2
- Composer
- MySQL/PostgreSQL sau SQLite
- Node.js și NPM (pentru assets)

### Pași de instalare

1. **Clonează repository-ul**
```bash
git clone [repository-url]
cd ION-CRM
```

2. **Instalează dependențele**
```bash
composer install
npm install
```

3. **Configurează aplicația**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurează baza de date în `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ion_crm
DB_USERNAME=root
DB_PASSWORD=
```

5. **Rulează migrările și seed-urile**
```bash
php artisan migrate
php artisan db:seed
```

6. **Compilează assets**
```bash
npm run build
```

7. **Configurează storage link**
```bash
php artisan storage:link
```

8. **Pornește serverul de dezvoltare**
```bash
php artisan serve
```

Aplicația va fi disponibilă la `http://localhost:8000`

### Credențiale Default

După rularea seed-urilor, se creează un utilizator de test:
- **Email:** test@example.com
- **Parolă:** (generată aleatoriu, verifică în seeder)

## 🔐 Roluri și Permisiuni

- **Administrator:** Acces complet la toate funcțiile
- **Manager:** Acces la proiecte și board-uri
- **Vânzări:** Acces limitat (a se implementa)
- **Utilizator:** Acces de bază (a se implementa)

## 📚 Documentație API

Pentru detalii despre integrarea Telegram, consultă `TELEGRAM_TESTING_GUIDE.md`.

## 🛠️ Structura Proiectului

```
ION-CRM/
├── app/
│   ├── Livewire/
│   │   ├── Admin/        # Componente Livewire pentru admin
│   │   └── Public/       # Componente Livewire publice
│   ├── Models/           # Modele Eloquent
│   ├── Notifications/    # Clase de notificări
│   └── Helpers/          # Helper functions
├── database/
│   ├── migrations/       # Migrări baza de date
│   └── seeders/          # Seed-uri pentru date inițiale
├── resources/
│   ├── views/            # Blade templates
│   ├── sass/             # Styles SCSS
│   └── js/               # JavaScript
└── routes/
    └── web.php           # Rute web
```

## 🚧 Funcții Planificate pentru Viitor

### 📄 Proposals (Oferte)
- **Template-uri de oferte:** Creare și management template-uri personalizabile
- **Lista oferte:** Vizualizare și management oferte
- **Creare ofertă:** 
  - Titlu, Client asociat, Template selectat
  - Data ofertă, Valabil până la
  - Tag-uri pentru categorizare
  - Urmărire creator și updater
- **Trimitere email:** Sistem de trimitere oferte direct prin email
- **Status oferte:** Tracking status (draft, sent, accepted, rejected)

### 🌐 CMS pentru Pagini Publice
- **Creare pagini publice:** Editor pentru pagini statice
- **Management meniu:** Creare și configurare meniu navigare
- **URL-uri prietenoase:** SEO-friendly URLs
- **Templates pagini:** Sistem de template-uri pentru pagini

### 📊 Activitate Utilizatori
- **Jurnal de activitate:** Log detaliat al acțiunilor utilizatorilor
- **Audit trail:** Urmărire modificări și acțiuni critice
- **Rapoarte activitate:** Statistici și rapoarte per utilizator
- **Export activități:** Export în CSV/PDF

### 🔄 Actualizare Aplicație
- **Sistem de update automat:** Verificare și instalare update-uri
- **Notificări update:** Alertă pentru update-uri disponibile
- **Backup automat:** Backup înainte de update
- **Rollback:** Posibilitate revenire la versiune anterioară

### 🎨 Themes (Teme)
- **Multiple teme:** Sistem de schimbare teme
- **Customizare:** Personalizare culori și stiluri
- **Dark mode:** Mod întunecat pentru interfață
- **Theme editor:** Editor vizual pentru teme

### 🔍 SEO Optimizare
- **Meta tags:** Management meta tags pentru SEO
- **Sitemap:** Generare automată sitemap
- **Robots.txt:** Configurare robots.txt
- **Schema markup:** Markup structured data

### 📧 Integrări Viitoare
- **Calendar:** Integrare calendar pentru evenimente și deadline-uri
- **File sharing:** Sistem avansat de partajare fișiere
- **Time tracking:** Tracking timp lucrat pe task-uri/proiecte
- **Invoicing:** Generare și management facturi
- **Reports:** Rapoarte avansate și dashboard-uri personalizabile
- **API RESTful:** API pentru integrare cu sisteme externe
- **Webhooks:** Suport pentru webhooks
- **Multi-tenant:** Suport pentru multiple organizații

### 🔐 Securitate Avansată
- **Two-factor authentication (2FA):** Autentificare cu doi factori
- **IP whitelist:** Restricții acces pe bază de IP
- **Session management:** Management avansat sesiuni
- **Rate limiting:** Protecție împotriva atacurilor

### 📱 Mobile App
- **Aplicație mobile:** Aplicație nativă pentru iOS și Android
- **Push notifications:** Notificări push pentru mobile
- **Offline mode:** Funcționare offline cu sincronizare

## 🤝 Contribuții

Contribuțiile sunt binevenite! Te rugăm să:
1. Fork repository-ul
2. Creează o branch pentru feature (`git checkout -b feature/AmazingFeature`)
3. Commit schimbările (`git commit -m 'Add some AmazingFeature'`)
4. Push la branch (`git push origin feature/AmazingFeature`)
5. Deschide un Pull Request

## 📄 Licență

Acest proiect este licențiat sub MIT License - vezi fișierul `LICENSE` pentru detalii.

## 👨‍💻 Autor

ION CRM - Dezvoltat pentru management eficient al relațiilor cu clienții

## 📞 Suport

Pentru întrebări și suport, te rugăm să deschizi un issue în repository.

---

**Versiune:** 1.0.0  
**Ultima actualizare:** 2025
