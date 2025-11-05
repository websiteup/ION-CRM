<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Template-uri Proposals</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Template
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume sau subiect...">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Subiect</th>
                                <th>Default</th>
                                <th>Creat de</th>
                                <th>Actualizat</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->name }}</td>
                                    <td>{{ $template->subject ?? '-' }}</td>
                                    <td>
                                        @if($template->is_default)
                                            <span class="badge bg-success">Default</span>
                                        @else
                                            <button wire:click="setDefault({{ $template->id }})" class="btn btn-sm btn-outline-secondary">
                                                Setează default
                                            </button>
                                        @endif
                                    </td>
                                    <td>{{ $template->creator->name ?? '-' }}</td>
                                    <td>{{ $template->updated_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <button wire:click="openModal({{ $template->id }})" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="delete({{ $template->id }})" 
                                                wire:confirm="Ești sigur că vrei să ștergi acest template?" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nu există template-uri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $templateId ? 'Editează Template' : 'Adaugă Template' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="name" class="form-label">Nume Template *</label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" id="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" wire:model="is_default" id="is_default">
                                        <label class="form-check-label" for="is_default">
                                            Template Default
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="subject" class="form-label">Subiect Email</label>
                                    <input type="text" wire:model="subject" class="form-control @error('subject') is-invalid @enderror" id="subject" placeholder="Subiect pentru email-ul de trimis">
                                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="content_html" class="form-label">Conținut HTML *</label>
                                    <div wire:ignore>
                                        <textarea id="templateContent" class="form-control @error('content_html') is-invalid @enderror" rows="15"></textarea>
                                    </div>
                                    @error('content_html') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Folosește shortcode-urile de mai jos pentru a insera date dinamice</small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary" onclick="destroySummernote(); summernoteInitialized = false;">Anulează</button>
                        <button type="button" wire:click="save" class="btn btn-primary" onclick="
                            const editor = $('#templateContent');
                            if (editor.length && editor.summernote('code') !== undefined) {
                                @this.set('content_html', editor.summernote('code'), false);
                            }
                        ">Salvează</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcode-uri disponibile - Sidebar -->
        <div wire:ignore class="position-fixed" style="right: 20px; bottom: 20px; z-index: 1050; max-width: 400px;">
            <div class="card shadow-lg" id="shortcodesCard" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Shortcode-uri Disponibile</h6>
                    <button type="button" class="btn-close" onclick="document.getElementById('shortcodesCard').style.display='none'"></button>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="mb-2">
                        <strong>Informații Client:</strong>
                        <ul class="list-unstyled ms-3 small">
                            <li><code>@{{client_name}}</code> - Numele complet</li>
                            <li><code>@{{client_first_name}}</code> - Prenumele</li>
                            <li><code>@{{client_last_name}}</code> - Numele de familie</li>
                            <li><code>@{{client_email}}</code> - Email</li>
                            <li><code>@{{client_phone}}</code> - Telefon</li>
                            <li><code>@{{client_address}}</code> - Adresa</li>
                        </ul>
                    </div>
                    <div class="mb-2">
                        <strong>Informații Ofertă:</strong>
                        <ul class="list-unstyled ms-3 small">
                            <li><code>@{{proposal_number}}</code> - Număr ofertă</li>
                            <li><code>@{{proposal_date}}</code> - Data ofertei</li>
                            <li><code>@{{valid_until}}</code> - Data expirării</li>
                            <li><code>@{{proposal_title}}</code> - Titlul</li>
                        </ul>
                    </div>
                    <div class="mb-2">
                        <strong>Informații Companie:</strong>
                        <ul class="list-unstyled ms-3 small">
                            <li><code>@{{company_name}}</code> - Nume</li>
                            <li><code>@{{company_email}}</code> - Email</li>
                            <li><code>@{{company_phone}}</code> - Telefon</li>
                            <li><code>@{{company_address}}</code> - Adresa</li>
                        </ul>
                    </div>
                    <div class="mb-2">
                        <strong>Tabele și Totaluri:</strong>
                        <ul class="list-unstyled ms-3 small">
                            <li><code>@{{items_table}}</code> - Tabel servicii</li>
                            <li><code>@{{subtotal}}</code> - Subtotal</li>
                            <li><code>@{{tax_total}}</code> - Taxe</li>
                            <li><code>@{{total}}</code> - Total</li>
                            <li><code>@{{currency_symbol}}</code> - Simbol valută</li>
                        </ul>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <strong>Notă:</strong> Shortcode-urile sunt înlocuite automat.
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-info btn-sm" onclick="document.getElementById('shortcodesCard').style.display = document.getElementById('shortcodesCard').style.display === 'none' ? 'block' : 'none'">
                <i class="bi bi-question-circle"></i> Shortcode-uri
            </button>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let summernoteInitialized = false;
    let summernoteUpdateTimeout = null;

    function initializeSummernote() {
        const $editor = $('#templateContent');
        
        if (!$editor.length) {
            console.log('Summernote: Textarea nu există');
            return;
        }
        
        // Verificăm dacă Summernote este deja inițializat
        if (summernoteInitialized) {
            try {
                if ($editor.summernote('code') !== undefined) {
                    // Deja inițializat, doar actualizăm conținutul dacă este necesar
                    const currentContent = @this.get('content_html') || '';
                    const editorContent = $editor.summernote('code');
                    if (currentContent && editorContent !== currentContent) {
                        $editor.summernote('code', currentContent);
                    }
                    return;
                }
            } catch (e) {
                // Summernote nu este inițializat corect, continuăm
                summernoteInitialized = false;
            }
        }
        
        // Distrugem orice instanță existentă (dacă există)
        try {
            if ($editor.data('summernote')) {
                $editor.summernote('destroy');
            }
        } catch (e) {
            // Ignorăm erorile
        }
        
        // Obținem conținutul din Livewire
        const initialContent = @this.get('content_html') || '';
        
        console.log('Summernote: Inițializare cu conținut:', initialContent ? 'da' : 'nu');
        
        // Inițializăm Summernote
        $editor.summernote({
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function(contents) {
                    // Actualizăm Livewire doar când utilizatorul termină de editat
                    clearTimeout(summernoteUpdateTimeout);
                    summernoteUpdateTimeout = setTimeout(() => {
                        @this.set('content_html', contents, false);
                    }, 500);
                },
                onBlur: function() {
                    // Actualizăm Livewire când utilizatorul părăsește editorul
                    const content = $editor.summernote('code');
                    @this.set('content_html', content, false);
                }
            }
        });
        
        // Setăm conținutul dacă există (după un mic delay pentru a fi sigur că Summernote este gata)
        if (initialContent) {
            setTimeout(() => {
                $editor.summernote('code', initialContent);
            }, 200);
        }
        
        summernoteInitialized = true;
        console.log('Summernote: Inițializat cu succes');
    }

    function destroySummernote() {
        const $editor = $('#templateContent');
        if ($editor.length) {
            try {
                if ($editor.summernote('code') !== undefined) {
                    $editor.summernote('destroy');
                }
            } catch (e) {
                // Ignorăm erorile
            }
        }
        summernoteInitialized = false;
    }

    // Inițializare când Livewire se încarcă
    document.addEventListener('livewire:init', () => {
        // Listen pentru event-ul emis de Livewire când se deschide modalul
        Livewire.on('modal-opened', () => {
            summernoteInitialized = false; // Resetăm flag-ul
            setTimeout(() => {
                initializeSummernote();
            }, 400);
        });
        
        // Obținem conținutul din Summernote înainte de a închide modalul
        Livewire.on('get-summernote-content-before-close', () => {
            const $editor = $('#templateContent');
            if ($editor.length) {
                try {
                    if ($editor.summernote('code') !== undefined) {
                        const content = $editor.summernote('code');
                        @this.set('content_html', content, false);
                    }
                } catch (e) {
                    // Ignorăm erorile
                }
            }
        });
    });
    
    // Listen pentru actualizările Livewire (doar când modalul este deschis)
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            // Verificăm dacă modalul este deschis și textarea există
            // Dar NU reinițializăm dacă Summernote este deja activ
            const modal = document.querySelector('.modal.show');
            const textarea = document.getElementById('templateContent');
            
            if (modal && textarea && !summernoteInitialized) {
                // Doar dacă nu este deja inițializat
                setTimeout(() => {
                    initializeSummernote();
                }, 400);
            }
        });
    });

    // Observăm schimbările în DOM pentru a detecta când modalul se deschide
    document.addEventListener('DOMContentLoaded', function() {
        let lastModalState = false;
        
        const observer = new MutationObserver(function(mutations) {
            const modal = document.querySelector('.modal.show');
            const textarea = document.getElementById('templateContent');
            const modalIsOpen = !!modal;
            
            // Doar dacă starea modalului s-a schimbat
            if (modalIsOpen !== lastModalState) {
                lastModalState = modalIsOpen;
                
                if (modalIsOpen && textarea && !summernoteInitialized) {
                    // Modalul tocmai s-a deschis
                    setTimeout(() => {
                        initializeSummernote();
                    }, 400);
                } else if (!modalIsOpen && summernoteInitialized) {
                    // Modalul s-a închis
                    destroySummernote();
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
    });
</script>
@endpush



