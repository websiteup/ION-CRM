# Integrare Google Calendar

## Instalare

1. **Instalează pachetul Google API Client:**
   ```bash
   composer require google/apiclient
   ```

2. **Rulează migrațiile:**
   ```bash
   php artisan migrate
   ```

3. **Configurează Google OAuth:**

   - Mergi la [Google Cloud Console](https://console.cloud.google.com/)
   - Creează un proiect nou sau selectează unul existent
   - Activează Google Calendar API
   - Creează OAuth 2.0 credentials
   - Adaugă redirect URI: `https://yourdomain.com/admin/calendar/callback`
   - Copiază Client ID și Client Secret

4. **Adaugă în `.env`:**
   ```env
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   GOOGLE_REDIRECT_URI=https://yourdomain.com/admin/calendar/callback
   ```

## Utilizare

1. **Conectează-te la Google Calendar:**
   - Mergi la `/admin/calendar`
   - Apasă butonul "Conectează Google Calendar"
   - Autorizează aplicația

2. **Sincronizare automată:**
   - Task-urile cu `due_date` și `assigned_to` se sincronizează automat cu Google Calendar
   - Culoarea evenimentului depinde de prioritatea task-ului:
     - Urgent: Roșu
     - High: Portocaliu
     - Medium: Galben
     - Low: Verde

3. **Sincronizare manuală:**
   - Apasă butonul "Sincronizează cu Google Calendar" pentru a sincroniza toate task-urile

## Funcționalități

- ✅ Afișare calendar cu task-uri
- ✅ Sincronizare automată la creare/actualizare task
- ✅ Sincronizare manuală pentru toate task-urile
- ✅ Deconectare Google Calendar
- ✅ Culori bazate pe prioritate
- ✅ Link către task din calendar

## Note

- Doar task-urile cu `due_date` sunt sincronizate
- Sincronizarea se face pentru utilizatorul atribuit task-ului
- Token-urile OAuth sunt reîmprospătate automat când expiră

