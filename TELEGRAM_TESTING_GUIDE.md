# Ghid de Testare Notificări Telegram

## Opțiuni de Testare

### 1. Testare Simplă (Mesaj Direct)

Trimite un mesaj simplu de testare direct către bot:

```bash
php artisan telegram:test --simple
```

**Ce face:**
- Verifică dacă token-ul bot-ului este configurat
- Caută ultimul chat_id din conversațiile cu bot-ul
- Trimite un mesaj de testare simplu

**Instrucțiuni:**
1. Asigură-te că ai configurat token-ul în Setări → General → Telegram Bot Token
2. Începe o conversație cu bot-ul Telegram trimițând `/start`
3. Rulează comanda

---

### 2. Testare Notificări cu Task Real

Testează notificările folosind task-uri reale din sistem:

#### Test Notificare Task Creat:
```bash
php artisan telegram:test --user=1 --type=created
```

#### Test Notificare Task Atribuit:
```bash
php artisan telegram:test --user=1 --type=assigned
```

#### Test Notificare Task Actualizat:
```bash
php artisan telegram:test --user=1 --type=updated
```

#### Test Notificare Deadline:
```bash
php artisan telegram:test --user=1 --type=deadline
```

**Parametri:**
- `--user=ID` - ID-ul utilizatorului pentru care vrei să testezi
- `--type=TIP` - Tipul notificării (created, assigned, updated, deadline)

**Instrucțiuni:**
1. Găsește ID-ul utilizatorului (poți folosi `php artisan tinker` și `User::all()`)
2. Asigură-te că utilizatorul are:
   - `telegram_chat_id` configurat (din Profil → Conectare Telegram)
   - `notification_telegram_enabled = true`
   - Tipul de notificare activat (de ex. `notification_task_created = true`)
3. Asigură-te că există cel puțin un proiect și un board în sistem
4. Rulează comanda

---

### 3. Testare Manuală prin Interfață

#### Testare prin crearea unui task:
1. Mergi la Board-uri → Selectează un board
2. Creează un task nou
3. Atribuie task-ul unui utilizator care are:
   - Telegram Chat ID configurat
   - Notificări Telegram activate
   - Notificări pentru "Task creat" și "Task atribuit" activate
4. Verifică Telegram-ul pentru notificări

#### Testare prin actualizarea unui task:
1. Deschide un task existent
2. Modifică informațiile (de ex. titlul, prioritatea, data scadență)
3. Salvează modificările
4. Verifică Telegram-ul pentru notificare

---

### 4. Testare prin Laravel Tinker

Pentru testare rapidă și debugging:

```bash
php artisan tinker
```

Apoi în Tinker:

```php
// Găsește un utilizator
$user = User::find(1);

// Verifică configurația
$user->telegram_chat_id;
$user->notification_telegram_enabled;
$user->notification_task_created;

// Creează un task de test
$task = Task::first();
$task->load(['column.board', 'project', 'assignedUser', 'creator']);

// Trimite notificare
$user->notify(new \App\Notifications\TaskCreatedNotification($task));
```

---

## Verificare Configurație

### 1. Verifică Token-ul Bot
```bash
php artisan tinker
```
```php
$settings = \App\Models\Setting::first();
$settings->telegram_bot_token; // Ar trebui să returneze token-ul
```

### 2. Verifică Chat ID Utilizator
```php
$user = \App\Models\User::find(1);
$user->telegram_chat_id; // Ar trebui să returneze chat_id-ul
```

### 3. Testează Conectivitatea cu Telegram API
```bash
php artisan tinker
```
```php
$settings = \App\Models\Setting::first();
$response = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$settings->telegram_bot_token}/getMe");
$response->json(); // Ar trebui să returneze informații despre bot
```

---

## Debugging Probleme

### Problema: Nu primesc notificări

**Verifică:**
1. ✅ Token-ul bot-ului este configurat corect în Setări
2. ✅ Utilizatorul are `telegram_chat_id` setat
3. ✅ `notification_telegram_enabled = true` pentru utilizator
4. ✅ Tipul de notificare este activat (de ex. `notification_task_created = true`)
5. ✅ Task-ul are toate relațiile încărcate (board, project, column)
6. ✅ Bot-ul funcționează (testează cu `--simple`)

### Problema: Eroare "Chat not found"

**Soluție:**
- Utilizatorul trebuie să înceapă o conversație cu bot-ul trimițând `/start`
- Verifică dacă `telegram_chat_id` este corect în baza de date
- Testează cu `--simple` pentru a obține chat_id corect

### Problema: Token invalid

**Soluție:**
- Verifică token-ul în Setări → General → Telegram Bot Token
- Asigură-te că token-ul este complet (în format `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)
- Creează un bot nou cu @BotFather dacă este necesar

---

## Exemple Complete

### Exemplu 1: Test complet pentru utilizatorul cu ID 1
```bash
# 1. Test simplu
php artisan telegram:test --simple

# 2. Test notificare task creat
php artisan telegram:test --user=1 --type=created

# 3. Test notificare task atribuit
php artisan telegram:test --user=1 --type=assigned

# 4. Test notificare task actualizat
php artisan telegram:test --user=1 --type=updated

# 5. Test notificare deadline
php artisan telegram:test --user=1 --type=deadline
```

### Exemplu 2: Testare prin Tinker
```bash
php artisan tinker
```

```php
// Găsește utilizatorul
$user = \App\Models\User::find(1);

// Activează notificările dacă nu sunt activate
$user->notification_telegram_enabled = true;
$user->notification_task_created = true;
$user->notification_task_assigned = true;
$user->save();

// Obține un task existent
$task = \App\Models\Task::with(['column.board', 'project', 'assignedUser', 'creator'])->first();

// Trimite notificare
$user->notify(new \App\Notifications\TaskCreatedNotification($task));
```

---

## Loguri și Monitoring

Verifică logurile Laravel pentru erori:
```bash
tail -f storage/logs/laravel.log
```

Sau în Windows:
```powershell
Get-Content storage/logs/laravel.log -Tail 50 -Wait
```

---

## Suport

Dacă întâmpini probleme:
1. Verifică logurile Laravel
2. Testează cu `--simple` pentru a verifica conectivitatea
3. Verifică configurația utilizatorului în baza de date
4. Asigură-te că bot-ul funcționează testând manual în Telegram

