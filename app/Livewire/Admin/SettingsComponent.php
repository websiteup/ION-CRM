<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use App\Models\Company;
use App\Models\TaxRate;
use App\Models\Currency;
use App\Models\Language;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SettingsComponent extends Component
{
    use WithFileUploads;

    // Tab active
    public $activeTab = 'general';

    // General Settings
    public $app_name = '';
    public $default_language = 'ro';
    public $timezone = 'Europe/Bucharest';
    public $date_format = 'd/m/Y';
    public $app_logo;
    public $app_logo_preview = null;
    public $smtp_host = '';
    public $smtp_port = 587;
    public $smtp_username = '';
    public $smtp_password = '';
    public $smtp_encryption = 'tls';
    public $smtp_from_name = '';
    public $smtp_from_email = '';
    public $telegram_bot_token = '';

    // Company Settings
    public $company_name = '';
    public $company_phone = '';
    public $company_email = '';
    public $company_address = '';
    public $company_logo;
    public $company_logo_preview = null;
    public $invoice_prefix = 'INV-';
    public $proforma_prefix = 'PROF-';

    // Tax Rate Modal
    public $tax_rate_id = null;
    public $tax_rate_name = '';
    public $tax_rate_value = 0;
    public $tax_rate_description = '';
    public $tax_rate_is_default = false;
    public $show_tax_modal = false;

    // Currency Modal
    public $currency_id = null;
    public $currency_code = '';
    public $currency_name = '';
    public $currency_symbol = '';
    public $currency_rate = 1.0000;
    public $currency_is_default = false;
    public $show_currency_modal = false;

    // Language Toggle
    public $language_id = null;
    public $language_is_active = false;

    protected $rules = [
        // General
        'app_name' => 'required|string|max:255',
        'default_language' => 'required|string|max:10',
        'timezone' => 'required|string|max:100',
        'date_format' => 'required|string|max:20',
        'app_logo' => 'nullable|image|max:2048',
        'smtp_host' => 'nullable|string|max:255',
        'smtp_port' => 'nullable|integer|min:1|max:65535',
        'smtp_username' => 'nullable|string|max:255',
        'smtp_password' => 'nullable|string|max:255',
        'smtp_encryption' => 'nullable|in:tls,ssl',
        'smtp_from_name' => 'nullable|string|max:255',
        'smtp_from_email' => 'nullable|email|max:255',
        'telegram_bot_token' => 'nullable|string|max:255',
        // Company
        'company_name' => 'required|string|max:255',
        'company_phone' => 'nullable|string|max:50',
        'company_email' => 'nullable|email|max:255',
        'company_address' => 'nullable|string',
        'company_logo' => 'nullable|image|max:2048',
        'invoice_prefix' => 'required|string|max:20',
        'proforma_prefix' => 'required|string|max:20',
        // Tax Rate
        'tax_rate_name' => 'required|string|max:255',
        'tax_rate_value' => 'required|numeric|min:0|max:100',
        'tax_rate_description' => 'nullable|string',
        // Currency
        'currency_code' => 'required|string|size:3|unique:currencies,code',
        'currency_name' => 'required|string|max:255',
        'currency_symbol' => 'required|string|max:10',
        'currency_rate' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }

        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Load General Settings
        $settings = Setting::getSettings();
        $this->app_name = $settings->app_name;
        $this->default_language = $settings->default_language;
        $this->timezone = $settings->timezone;
        $this->date_format = $settings->date_format;
        $this->app_logo_preview = $settings->app_logo ? asset('storage/' . $settings->app_logo) : null;
        $this->smtp_host = $settings->smtp_host ?? '';
        $this->smtp_port = $settings->smtp_port ?? 587;
        $this->smtp_username = $settings->smtp_username ?? '';
        $this->smtp_password = $settings->smtp_password ?? '';
        $this->smtp_encryption = $settings->smtp_encryption ?? 'tls';
        $this->smtp_from_name = $settings->smtp_from_name ?? '';
        $this->smtp_from_email = $settings->smtp_from_email ?? '';
        $this->telegram_bot_token = $settings->telegram_bot_token ?? '';

        // Load Company Settings
        $company = Company::getCompany();
        $this->company_name = $company->name ?? '';
        $this->company_phone = $company->phone ?? '';
        $this->company_email = $company->email ?? '';
        $this->company_address = $company->address ?? '';
        $this->company_logo_preview = $company->company_logo ? asset('storage/' . $company->company_logo) : null;
        $this->invoice_prefix = $company->invoice_prefix ?? 'INV-';
        $this->proforma_prefix = $company->proforma_prefix ?? 'PROF-';
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['app_logo', 'company_logo'])) {
            $this->validateOnly($propertyName);
            if ($propertyName === 'app_logo' && $this->app_logo) {
                $this->app_logo_preview = $this->app_logo->temporaryUrl();
            } elseif ($propertyName === 'company_logo' && $this->company_logo) {
                $this->company_logo_preview = $this->company_logo->temporaryUrl();
            }
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral()
    {
        $this->validate([
            'app_name' => 'required|string|max:255',
            'default_language' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            'app_logo' => 'nullable|image|max:2048',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'smtp_from_name' => 'nullable|string|max:255',
            'smtp_from_email' => 'nullable|email|max:255',
            'telegram_bot_token' => 'nullable|string|max:255',
        ]);

        $settings = Setting::getSettings();
        $data = [
            'app_name' => $this->app_name,
            'default_language' => $this->default_language,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'smtp_encryption' => $this->smtp_encryption,
            'smtp_from_name' => $this->smtp_from_name,
            'smtp_from_email' => $this->smtp_from_email,
            'telegram_bot_token' => $this->telegram_bot_token,
        ];

        // Only update password if a new one was provided
        if (!empty($this->smtp_password)) {
            $data['smtp_password'] = $this->smtp_password;
        }

        if ($this->app_logo) {
            if ($settings->app_logo) {
                Storage::disk('public')->delete($settings->app_logo);
            }
            $logoPath = $this->app_logo->store('settings', 'public');
            $data['app_logo'] = $logoPath;
        }

        $settings->update($data);
        $this->app_logo = null; // Reset file input
        $this->smtp_password = ''; // Clear password field
        
        // Clear config cache to apply new settings
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        
        notify()->success('Setări generale actualizate cu succes!');
    }

    public function saveCompany()
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string',
            'company_logo' => 'nullable|image|max:2048',
            'invoice_prefix' => 'required|string|max:20',
            'proforma_prefix' => 'required|string|max:20',
        ]);

        $company = Company::getCompany();
        $data = [
            'name' => $this->company_name,
            'phone' => $this->company_phone,
            'email' => $this->company_email,
            'address' => $this->company_address,
            'invoice_prefix' => $this->invoice_prefix,
            'proforma_prefix' => $this->proforma_prefix,
        ];

        if ($this->company_logo) {
            if ($company->company_logo) {
                Storage::disk('public')->delete($company->company_logo);
            }
            $logoPath = $this->company_logo->store('settings', 'public');
            $data['company_logo'] = $logoPath;
        }

        $company->update($data);
        $this->company_logo = null; // Reset file input
        notify()->success('Detalii companie actualizate cu succes!');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        notify()->success('Cache-ul a fost șters cu succes!');
    }

    // Tax Rate Methods
    public function openTaxModal($id = null)
    {
        if ($id) {
            $taxRate = TaxRate::findOrFail($id);
            $this->tax_rate_id = $id;
            $this->tax_rate_name = $taxRate->name;
            $this->tax_rate_value = $taxRate->rate;
            $this->tax_rate_description = $taxRate->description;
            $this->tax_rate_is_default = $taxRate->is_default;
        } else {
            $this->resetTaxForm();
        }
        $this->show_tax_modal = true;
    }

    public function closeTaxModal()
    {
        $this->show_tax_modal = false;
        $this->resetTaxForm();
    }

    public function resetTaxForm()
    {
        $this->tax_rate_id = null;
        $this->tax_rate_name = '';
        $this->tax_rate_value = 0;
        $this->tax_rate_description = '';
        $this->tax_rate_is_default = false;
        $this->resetValidation();
    }

    public function saveTaxRate()
    {
        $rules = [
            'tax_rate_name' => 'required|string|max:255',
            'tax_rate_value' => 'required|numeric|min:0|max:100',
            'tax_rate_description' => 'nullable|string',
        ];
        $this->validate($rules);

        $data = [
            'name' => $this->tax_rate_name,
            'rate' => $this->tax_rate_value,
            'description' => $this->tax_rate_description,
            'is_default' => $this->tax_rate_is_default,
        ];

        if ($this->tax_rate_id) {
            TaxRate::findOrFail($this->tax_rate_id)->update($data);
            notify()->success('Tax rate actualizat cu succes!');
        } else {
            TaxRate::create($data);
            notify()->success('Tax rate adăugat cu succes!');
        }

        $this->closeTaxModal();
    }

    public function deleteTaxRate($id)
    {
        TaxRate::findOrFail($id)->delete();
        notify()->success('Tax rate șters cu succes!');
    }

    // Currency Methods
    public function openCurrencyModal($id = null)
    {
        if ($id) {
            $currency = Currency::findOrFail($id);
            $this->currency_id = $id;
            $this->currency_code = $currency->code;
            $this->currency_name = $currency->name;
            $this->currency_symbol = $currency->symbol;
            $this->currency_rate = $currency->rate;
            $this->currency_is_default = $currency->is_default;
        } else {
            $this->resetCurrencyForm();
        }
        $this->show_currency_modal = true;
    }

    public function closeCurrencyModal()
    {
        $this->show_currency_modal = false;
        $this->resetCurrencyForm();
    }

    public function resetCurrencyForm()
    {
        $this->currency_id = null;
        $this->currency_code = '';
        $this->currency_name = '';
        $this->currency_symbol = '';
        $this->currency_rate = 1.0000;
        $this->currency_is_default = false;
        $this->resetValidation();
    }

    public function saveCurrency()
    {
        $uniqueRule = $this->currency_id 
            ? 'required|string|size:3|unique:currencies,code,' . $this->currency_id
            : 'required|string|size:3|unique:currencies,code';
        
        $rules = [
            'currency_code' => $uniqueRule,
            'currency_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
            'currency_rate' => 'required|numeric|min:0',
        ];
        $this->validate($rules);

        $data = [
            'code' => strtoupper($this->currency_code),
            'name' => $this->currency_name,
            'symbol' => $this->currency_symbol,
            'rate' => $this->currency_rate,
            'is_default' => $this->currency_is_default,
        ];

        if ($this->currency_id) {
            Currency::findOrFail($this->currency_id)->update($data);
            notify()->success('Currency actualizat cu succes!');
        } else {
            Currency::create($data);
            notify()->success('Currency adăugat cu succes!');
        }

        $this->closeCurrencyModal();
    }

    public function deleteCurrency($id)
    {
        Currency::findOrFail($id)->delete();
        notify()->success('Currency șters cu succes!');
    }

    // Language Methods
    public function toggleLanguage($id)
    {
        $language = Language::findOrFail($id);
        $language->update(['is_active' => !$language->is_active]);
        notify()->success('Status limba actualizat cu succes!');
    }

    public function setDefaultLanguage($id)
    {
        $language = Language::findOrFail($id);
        $language->update(['is_default' => true]);
        notify()->success('Limbă default setată cu succes!');
    }

    public function render()
    {
        $taxRates = TaxRate::orderBy('is_default', 'desc')->orderBy('rate', 'desc')->get();
        $currencies = Currency::orderBy('is_default', 'desc')->orderBy('code')->get();
        $languages = Language::orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('livewire.admin.settings-component', [
            'taxRates' => $taxRates,
            'currencies' => $currencies,
            'languages' => $languages,
        ])->layout('layouts.app');
    }
}

