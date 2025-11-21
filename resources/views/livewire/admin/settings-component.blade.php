<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Setări</h2>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" wire:click="switchTab('general')" type="button">
                    <i class="bi bi-gear"></i> General
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'company' ? 'active' : '' }}" wire:click="switchTab('company')" type="button">
                    <i class="bi bi-building"></i> Companie
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'tax' ? 'active' : '' }}" wire:click="switchTab('tax')" type="button">
                    <i class="bi bi-percent"></i> Taxe
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'currency' ? 'active' : '' }}" wire:click="switchTab('currency')" type="button">
                    <i class="bi bi-currency-exchange"></i> Monede
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'languages' ? 'active' : '' }}" wire:click="switchTab('languages')" type="button">
                    <i class="bi bi-translate"></i> Limbi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'email' ? 'active' : '' }}" wire:click="switchTab('email')" type="button">
                    <i class="bi bi-envelope"></i> Email
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'google_calendar' ? 'active' : '' }}" wire:click="switchTab('google_calendar')" type="button">
                    <i class="bi bi-calendar-event"></i> Google Calendar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'system' ? 'active' : '' }}" wire:click="switchTab('system')" type="button">
                    <i class="bi bi-hdd-network"></i> Platformă & Server
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- General Tab -->
            @if($activeTab === 'general')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Setări Generale</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveGeneral">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="app_name" class="form-label">Nume Aplicație</label>
                                    <input type="text" wire:model="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name">
                                    @error('app_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="default_language" class="form-label">Limbă Default</label>
                                    <select wire:model="default_language" class="form-select @error('default_language') is-invalid @enderror" id="default_language">
                                        <option value="ro">Română</option>
                                        <option value="en">English</option>
                                        <option value="de">Deutsch</option>
                                    </select>
                                    @error('default_language') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select wire:model="timezone" class="form-select @error('timezone') is-invalid @enderror" id="timezone">
                                        <option value="Europe/Bucharest">Bucharest (UTC+2)</option>
                                        <option value="Europe/London">London (UTC+0)</option>
                                        <option value="Europe/Berlin">Berlin (UTC+1)</option>
                                        <option value="America/New_York">New York (UTC-5)</option>
                                    </select>
                                    @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_format" class="form-label">Format Dată</label>
                                    <select wire:model="date_format" class="form-select @error('date_format') is-invalid @enderror" id="date_format">
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="d.m.Y">DD.MM.YYYY</option>
                                    </select>
                                    @error('date_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="app_logo" class="form-label">Logo Aplicație</label>
                                <input type="file" wire:model="app_logo" class="form-control @error('app_logo') is-invalid @enderror" id="app_logo" accept="image/*">
                                @error('app_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($app_logo_preview)
                                    <div class="mt-2">
                                        <img src="{{ $app_logo_preview }}" alt="App Logo" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                    </div>
                                @endif
                            </div>
                            <hr>
                            <h6 class="mb-3">Setări Telegram</h6>
                            <div class="mb-3">
                                <label for="telegram_bot_token" class="form-label">Telegram Bot Token</label>
                                <input type="text" wire:model="telegram_bot_token" class="form-control @error('telegram_bot_token') is-invalid @enderror" id="telegram_bot_token" placeholder="Obține token-ul de la @BotFather pe Telegram">
                                @error('telegram_bot_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Obține token-ul creând un bot nou folosind @BotFather pe Telegram.</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" wire:click="clearCache" class="btn btn-warning">
                                    <i class="bi bi-trash"></i> Clear Cache
                                </button>
                                <button type="button" wire:click="saveGeneral" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvează
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Platform & Server Tab -->
            @if($activeTab === 'system')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informații Platformă & Server</h5>
                        <span class="badge bg-secondary">read-only</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-4 col-md-6">
                                <h6 class="text-uppercase text-muted">Platformă</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted">Nume aplicație</th>
                                                <td>{{ $platformInfo['app_name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Versiune aplicație</th>
                                                <td>{{ $platformInfo['app_version'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Laravel</th>
                                                <td>{{ $platformInfo['laravel_version'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Mediu rulare</th>
                                                <td><span class="badge bg-info">{{ $platformInfo['app_env'] ?? 'N/A' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">URL aplicație</th>
                                                <td><a href="{{ $platformInfo['app_url'] ?? '#' }}" target="_blank">{{ $platformInfo['app_url'] ?? '-' }}</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <h6 class="text-uppercase text-muted">Server</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted">PHP Version</th>
                                                <td>{{ $serverInfo['php_version'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">SAPI</th>
                                                <td>{{ $serverInfo['php_sapi'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">OS</th>
                                                <td>{{ $serverInfo['server_os'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Server Software</th>
                                                <td>{{ $serverInfo['server_software'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Limits</th>
                                                <td>
                                                    <div>Memory: {{ $serverInfo['memory_limit'] }}</div>
                                                    <div>Upload: {{ $serverInfo['upload_max_filesize'] }}</div>
                                                    <div>Post: {{ $serverInfo['post_max_size'] }}</div>
                                                    <div>Exec: {{ $serverInfo['max_execution_time'] }}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <h6 class="text-uppercase text-muted">Bază de date</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted">Conexiune</th>
                                                <td><span class="badge bg-dark">{{ $databaseInfo['connection'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Driver</th>
                                                <td>{{ $databaseInfo['driver'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Host</th>
                                                <td>{{ $databaseInfo['host'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Bază de date</th>
                                                <td>{{ $databaseInfo['database'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Port</th>
                                                <td>{{ $databaseInfo['port'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Versiune server</th>
                                                <td>{{ $databaseInfo['version'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Email Tab -->
            @if($activeTab === 'email')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Setări Email SMTP</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveEmail">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_host" class="form-label">SMTP Host *</label>
                                    <input type="text" wire:model.live="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror" id="smtp_host" placeholder="smtp.example.com">
                                    @error('smtp_host') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Ex: smtp.gmail.com, smtp.mailtrap.io</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_port" class="form-label">SMTP Port *</label>
                                    <input type="number" wire:model.live="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror" id="smtp_port" placeholder="587">
                                    @error('smtp_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Porturi comune: 587 (TLS), 465 (SSL), 2525</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_username" class="form-label">SMTP Username</label>
                                    <input type="text" wire:model="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror" id="smtp_username" placeholder="username@example.com">
                                    @error('smtp_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_password" class="form-label">SMTP Password</label>
                                    <input type="password" wire:model="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror" id="smtp_password" placeholder="Lasă gol pentru a nu schimba">
                                    @error('smtp_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Lasă gol dacă nu vrei să schimbi parola existentă</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_encryption" class="form-label">Encryption *</label>
                                    <select wire:model="smtp_encryption" class="form-select @error('smtp_encryption') is-invalid @enderror" id="smtp_encryption">
                                        <option value="">None</option>
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                    </select>
                                    @error('smtp_encryption') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_from_name" class="form-label">From Name</label>
                                    <input type="text" wire:model="smtp_from_name" class="form-control @error('smtp_from_name') is-invalid @enderror" id="smtp_from_name" placeholder="ION CRM">
                                    @error('smtp_from_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="smtp_from_email" class="form-label">From Email *</label>
                                <input type="email" wire:model.live="smtp_from_email" class="form-control @error('smtp_from_email') is-invalid @enderror" id="smtp_from_email" placeholder="noreply@example.com">
                                @error('smtp_from_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Adresa de email de la care vor fi trimise email-urile</small>
                            </div>
                            <hr>
                            <h6 class="mb-3">Testare Email</h6>
                            <div class="mb-3">
                                <label for="test_email" class="form-label">Email de Test</label>
                                <input type="email" wire:model.live="test_email" class="form-control" id="test_email" placeholder="test@example.com">
                                <small class="form-text text-muted">Adresa de email la care vei primi email-ul de test</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                @php
                                    $canTest = !empty($smtp_host) && !empty($smtp_from_email) && !empty($test_email);
                                @endphp
                                @if(!$canTest)
                                    <button type="button" class="btn btn-info" disabled>
                                        <i class="bi bi-send"></i> Testează Email
                                        <small class="d-block text-muted">
                                            @if(empty($smtp_host) || empty($smtp_from_email))
                                                Completează SMTP Host și From Email pentru a testa
                                            @elseif(empty($test_email))
                                                Completează email-ul de test
                                            @endif
                                        </small>
                                    </button>
                                @else
                                    <button type="button" wire:click="testEmail" wire:loading.attr="disabled" class="btn btn-info">
                                        <span wire:loading.remove wire:target="testEmail">
                                            <i class="bi bi-send"></i> Testează Email
                                        </span>
                                        <span wire:loading wire:target="testEmail">
                                            <span class="spinner-border spinner-border-sm" role="status"></span> Se trimite...
                                        </span>
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvează Setări Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Google Calendar Tab -->
            @if($activeTab === 'google_calendar')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Setări Google Calendar API</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Instrucțiuni:</strong> Pentru a configura Google Calendar API:
                            <ol class="mb-0 mt-2">
                                <li>Mergi la <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                                <li>Creează un proiect nou sau selectează unul existent</li>
                                <li>Activează Google Calendar API</li>
                                <li>Creează OAuth 2.0 credentials (Application type: Web application)</li>
                                <li>Adaugă Redirect URI: <code>{{ $google_calendar_redirect_uri ?: (str_replace('localhost', '127.0.0.1', config('app.url')) . '/admin/calendar/callback') }}</code></li>
                                <li>Copiază Client ID și Client Secret aici</li>
                            </ol>
                        </div>
                        <form wire:submit.prevent="saveGoogleCalendar">
                            <div class="mb-3">
                                <label for="google_calendar_client_id" class="form-label">Client ID</label>
                                <input type="text" wire:model="google_calendar_client_id" class="form-control @error('google_calendar_client_id') is-invalid @enderror" id="google_calendar_client_id" placeholder="xxxxx.apps.googleusercontent.com">
                                @error('google_calendar_client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Client ID din Google Cloud Console</small>
                            </div>
                            <div class="mb-3">
                                <label for="google_calendar_client_secret" class="form-label">Client Secret</label>
                                <input type="password" wire:model="google_calendar_client_secret" class="form-control @error('google_calendar_client_secret') is-invalid @enderror" id="google_calendar_client_secret" placeholder="GOCSPX-xxxxx">
                                @error('google_calendar_client_secret') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Client Secret din Google Cloud Console</small>
                            </div>
                            <div class="mb-3">
                                <label for="google_calendar_redirect_uri" class="form-label">Redirect URI</label>
                                <input type="url" wire:model="google_calendar_redirect_uri" class="form-control @error('google_calendar_redirect_uri') is-invalid @enderror" id="google_calendar_redirect_uri" placeholder="{{ str_replace('localhost', '127.0.0.1', config('app.url')) }}/admin/calendar/callback">
                                @error('google_calendar_redirect_uri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">URI-ul de redirectare pentru OAuth (trebuie să fie același ca în Google Cloud Console)</small>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvează Setări Google Calendar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Company Tab -->
            @if($activeTab === 'company')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detalii Companie</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveCompany">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Nume Companie</label>
                                    <input type="text" wire:model="company_name" class="form-control @error('company_name') is-invalid @enderror" id="company_name">
                                    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="company_phone" class="form-label">Telefon</label>
                                    <input type="text" wire:model="company_phone" class="form-control @error('company_phone') is-invalid @enderror" id="company_phone">
                                    @error('company_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_email" class="form-label">Email</label>
                                    <input type="email" wire:model="company_email" class="form-control @error('company_email') is-invalid @enderror" id="company_email">
                                    @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="company_logo" class="form-label">Logo Companie</label>
                                    <input type="file" wire:model="company_logo" class="form-control @error('company_logo') is-invalid @enderror" id="company_logo" accept="image/*">
                                    @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($company_logo_preview)
                                        <div class="mt-2">
                                            <img src="{{ $company_logo_preview }}" alt="Company Logo" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Adresă</label>
                                <textarea wire:model="company_address" class="form-control @error('company_address') is-invalid @enderror" id="company_address" rows="3"></textarea>
                                @error('company_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <hr>
                            <h6 class="mb-3">Prefix-uri</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="invoice_prefix" class="form-label">Prefix Factură</label>
                                    <input type="text" wire:model="invoice_prefix" class="form-control @error('invoice_prefix') is-invalid @enderror" id="invoice_prefix">
                                    @error('invoice_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="proforma_prefix" class="form-label">Prefix Proformă</label>
                                    <input type="text" wire:model="proforma_prefix" class="form-control @error('proforma_prefix') is-invalid @enderror" id="proforma_prefix">
                                    @error('proforma_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <button type="button" wire:click="saveCompany" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvează
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Tax Rates Tab -->
            @if($activeTab === 'tax')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Taxe</h5>
                        <button wire:click="openTaxModal" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Adaugă Tax
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nume</th>
                                        <th>Rată (%)</th>
                                        <th>Descriere</th>
                                        <th>Default</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taxRates as $taxRate)
                                        <tr>
                                            <td>{{ $taxRate->name }}</td>
                                            <td>{{ number_format($taxRate->rate, 2) }}%</td>
                                            <td>{{ $taxRate->description ?? '-' }}</td>
                                            <td>
                                                @if($taxRate->is_default)
                                                    <span class="badge bg-success">Da</span>
                                                @else
                                                    <span class="badge bg-secondary">Nu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="openTaxModal({{ $taxRate->id }})" class="btn btn-sm btn-info">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteTaxRate({{ $taxRate->id }})" 
                                                        wire:confirm="Ești sigur că vrei să ștergi acest tax rate?" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Nu există tax rates.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Currency Tab -->
            @if($activeTab === 'currency')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Monede</h5>
                        <button wire:click="openCurrencyModal" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Adaugă Monedă
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cod</th>
                                        <th>Nume</th>
                                        <th>Simbol</th>
                                        <th>Rată</th>
                                        <th>Default</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currencies as $currency)
                                        <tr>
                                            <td><strong>{{ $currency->code }}</strong></td>
                                            <td>{{ $currency->name }}</td>
                                            <td>{{ $currency->symbol }}</td>
                                            <td>{{ number_format($currency->rate, 4) }}</td>
                                            <td>
                                                @if($currency->is_default)
                                                    <span class="badge bg-success">Da</span>
                                                @else
                                                    <span class="badge bg-secondary">Nu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="openCurrencyModal({{ $currency->id }})" class="btn btn-sm btn-info">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteCurrency({{ $currency->id }})" 
                                                        wire:confirm="Ești sigur că vrei să ștergi această monedă?" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Nu există monede.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Languages Tab -->
            @if($activeTab === 'languages')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Limbi Disponibile</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cod</th>
                                        <th>Nume</th>
                                        <th>Status</th>
                                        <th>Default</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($languages as $language)
                                        <tr>
                                            <td><strong>{{ $language->code }}</strong></td>
                                            <td>{{ $language->name }}</td>
                                            <td>
                                                @if($language->is_active)
                                                    <span class="badge bg-success">Activă</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactivă</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($language->is_default)
                                                    <span class="badge bg-primary">Da</span>
                                                @else
                                                    <span class="badge bg-secondary">Nu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="toggleLanguage({{ $language->id }})" 
                                                        class="btn btn-sm btn-{{ $language->is_active ? 'warning' : 'success' }}">
                                                    <i class="bi bi-toggle-{{ $language->is_active ? 'on' : 'off' }}"></i>
                                                    {{ $language->is_active ? 'Dezactivează' : 'Activează' }}
                                                </button>
                                                @if(!$language->is_default)
                                                    <button wire:click="setDefaultLanguage({{ $language->id }})" 
                                                            class="btn btn-sm btn-info">
                                                        <i class="bi bi-star"></i> Setează Default
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Nu există limbi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Tax Rate Modal -->
    @if($show_tax_modal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $tax_rate_id ? 'Editează Tax Rate' : 'Adaugă Tax Rate' }}</h5>
                        <button type="button" wire:click="closeTaxModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveTaxRate">
                            <div class="mb-3">
                                <label for="tax_rate_name" class="form-label">Nume</label>
                                <input type="text" wire:model="tax_rate_name" class="form-control @error('tax_rate_name') is-invalid @enderror" id="tax_rate_name">
                                @error('tax_rate_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="tax_rate_value" class="form-label">Rată (%)</label>
                                <input type="number" step="0.01" wire:model="tax_rate_value" class="form-control @error('tax_rate_value') is-invalid @enderror" id="tax_rate_value">
                                @error('tax_rate_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="tax_rate_description" class="form-label">Descriere</label>
                                <textarea wire:model="tax_rate_description" class="form-control @error('tax_rate_description') is-invalid @enderror" id="tax_rate_description" rows="3"></textarea>
                                @error('tax_rate_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" wire:model="tax_rate_is_default" class="form-check-input" id="tax_rate_is_default">
                                <label class="form-check-label" for="tax_rate_is_default">Default</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeTaxModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveTaxRate" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Currency Modal -->
    @if($show_currency_modal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $currency_id ? 'Editează Monedă' : 'Adaugă Monedă' }}</h5>
                        <button type="button" wire:click="closeCurrencyModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCurrency">
                            <div class="mb-3">
                                <label for="currency_code" class="form-label">Cod (3 caractere)</label>
                                <input type="text" wire:model="currency_code" class="form-control @error('currency_code') is-invalid @enderror" id="currency_code" maxlength="3" style="text-transform: uppercase;">
                                @error('currency_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currency_name" class="form-label">Nume</label>
                                <input type="text" wire:model="currency_name" class="form-control @error('currency_name') is-invalid @enderror" id="currency_name">
                                @error('currency_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Simbol</label>
                                <input type="text" wire:model="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol">
                                @error('currency_symbol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currency_rate" class="form-label">Rată de Schimb</label>
                                <input type="number" step="0.0001" wire:model="currency_rate" class="form-control @error('currency_rate') is-invalid @enderror" id="currency_rate">
                                @error('currency_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Rata față de moneda default (ex: 1 EUR = 4.95 RON)</small>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" wire:model="currency_is_default" class="form-check-input" id="currency_is_default">
                                <label class="form-check-label" for="currency_is_default">Monedă Default</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeCurrencyModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveCurrency" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

