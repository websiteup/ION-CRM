<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProposalTemplate;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProposalTemplatesComponent extends Component
{
    use WithPagination;

    public $name = '';
    public $subject = '';
    public $content_html = '';
    public $is_default = false;
    public $templateId = null;
    public $showModal = false;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'subject' => 'nullable|string|max:255',
        'content_html' => 'required|string',
        'is_default' => 'boolean',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function openModal($id = null)
    {
        if ($id) {
            $template = ProposalTemplate::findOrFail($id);
            $this->templateId = $id;
            $this->name = $template->name;
            $this->subject = $template->subject ?? '';
            // Escape Blade syntax to prevent interpretation
            $this->content_html = $template->content_html ?? '';
            $this->is_default = $template->is_default;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
        
        // Emit event pentru JavaScript
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        // Obținem conținutul din Summernote înainte de a închide
        $this->dispatch('get-summernote-content-before-close');
        
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->templateId = null;
        $this->name = '';
        $this->subject = '';
        $this->content_html = '';
        $this->is_default = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // Dacă este setat ca default, setează toate celelalte ca non-default
        if ($this->is_default) {
            ProposalTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $data = [
            'name' => $this->name,
            'subject' => $this->subject,
            'content_html' => $this->content_html,
            'is_default' => $this->is_default,
            'updated_by' => Auth::id(),
        ];

        if ($this->templateId) {
            $template = ProposalTemplate::findOrFail($this->templateId);
            $template->update($data);
            notify()->success('Template actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            ProposalTemplate::create($data);
            notify()->success('Template adăugat cu succes!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $template = ProposalTemplate::findOrFail($id);
        
        // Verifică dacă este folosit în proposals
        if ($template->proposals()->count() > 0) {
            notify()->error('Template-ul nu poate fi șters pentru că este folosit în oferte!');
            return;
        }

        $template->delete();
        notify()->success('Template șters cu succes!');
    }

    public function setDefault($id)
    {
        ProposalTemplate::where('is_default', true)->update(['is_default' => false]);
        $template = ProposalTemplate::findOrFail($id);
        $template->update(['is_default' => true]);
        notify()->success('Template setat ca default!');
    }

    public function render()
    {
        $query = ProposalTemplate::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%');
        }

        $templates = $query->with(['creator', 'updater'])->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.proposal-templates-component', [
            'templates' => $templates
        ]);
    }
}

