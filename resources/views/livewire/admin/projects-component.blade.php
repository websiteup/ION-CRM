<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Proiecte</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Proiect
            </button>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume proiect...">
                    </div>
                    <div class="col-md-4">
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">Toate statusurile</option>
                            <option value="not_started">Neinceput</option>
                            <option value="on_hold">În așteptare</option>
                            <option value="in_progress">În dezvoltare</option>
                            <option value="completed">Complet</option>
                            <option value="cancelled">Anulat</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select wire:model.live="clientFilter" class="form-select">
                            <option value="">Toți clienții</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Tip Facturare</th>
                                <th>Data Început</th>
                                <th>Data Sfârșit</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td><strong>{{ $project->name }}</strong></td>
                                    <td>{{ $project->client ? $project->client->first_name . ' ' . $project->client->last_name : '-' }}</td>
                                    <td>
                                        @if($project->status === 'not_started')
                                            <span class="badge bg-secondary">Neinceput</span>
                                        @elseif($project->status === 'on_hold')
                                            <span class="badge bg-warning">În așteptare</span>
                                        @elseif($project->status === 'in_progress')
                                            <span class="badge bg-primary">În dezvoltare</span>
                                        @elseif($project->status === 'completed')
                                            <span class="badge bg-success">Complet</span>
                                        @else
                                            <span class="badge bg-danger">Anulat</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->billing_type === 'fixed_rate')
                                            Rata Fixă
                                        @else
                                            Rata Orară
                                        @endif
                                    </td>
                                    <td>{{ $project->start_date ? $project->start_date->format('d.m.Y') : '-' }}</td>
                                    <td>{{ $project->end_date ? $project->end_date->format('d.m.Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.projects.view', $project->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Vezi
                                        </a>
                                        <button wire:click="openModal({{ $project->id }})" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="deleteProject({{ $project->id }})" 
                                                wire:confirm="Ești sigur că vrei să ștergi acest proiect?" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nu există proiecte.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Project Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $projectId ? 'Editează Proiect' : 'Adaugă Proiect' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveProject">
                            <div class="mb-3">
                                <label for="projectName" class="form-label">Nume Proiect <span class="text-danger">*</span></label>
                                <input type="text" wire:model="projectName" class="form-control @error('projectName') is-invalid @enderror" id="projectName">
                                @error('projectName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="clientId" class="form-label">Client</label>
                                    <select wire:model="clientId" class="form-select @error('clientId') is-invalid @enderror" id="clientId">
                                        <option value="">Selectează client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('clientId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" wire:click="openClientModal" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Adaugă Client
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" wire:model="clientPortalAccess" class="form-check-input" id="clientPortalAccess">
                                <label class="form-check-label" for="clientPortalAccess">Portal Client (clientul poate vizualiza detalii despre proiect)</label>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror" id="status">
                                    <option value="not_started">Neinceput</option>
                                    <option value="on_hold">În așteptare</option>
                                    <option value="in_progress">În dezvoltare</option>
                                    <option value="completed">Complet</option>
                                    <option value="cancelled">Anulat</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="billingType" class="form-label">Tipul de Facturare <span class="text-danger">*</span></label>
                                    <select wire:model="billingType" class="form-select @error('billingType') is-invalid @enderror" id="billingType">
                                        <option value="fixed_rate">Rata Fixă</option>
                                        <option value="hourly_rate">Rata Orară</option>
                                    </select>
                                    @error('billingType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="currencyId" class="form-label">Valută <span class="text-danger">*</span></label>
                                    <select wire:model="currencyId" class="form-select @error('currencyId') is-invalid @enderror" id="currencyId">
                                        <option value="">Selectează</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }} ({{ $currency->symbol }})</option>
                                        @endforeach
                                    </select>
                                    @error('currencyId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="{{ $billingType === 'fixed_rate' ? 'fixedRate' : 'hourlyRate' }}" class="form-label">
                                        {{ $billingType === 'fixed_rate' ? 'Rata Fixă' : 'Rata Orară' }} <span class="text-danger">*</span>
                                    </label>
                                    @if($billingType === 'fixed_rate')
                                        <input type="number" step="0.01" wire:model="fixedRate" class="form-control @error('fixedRate') is-invalid @enderror" id="fixedRate">
                                        @error('fixedRate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @else
                                        <input type="number" step="0.01" wire:model="hourlyRate" class="form-control @error('hourlyRate') is-invalid @enderror" id="hourlyRate">
                                        @error('hourlyRate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Data de Început (zi.luna.an)</label>
                                    <input type="text" wire:model="startDate" class="form-control" id="startDate" placeholder="04.11.2025">
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">Data de Sfârșit (zi.luna.an)</label>
                                    <input type="text" wire:model="endDate" class="form-control" id="endDate" placeholder="04.11.2025">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="descriptionHtml" class="form-label">Descriere Proiect</label>
                                <textarea wire:model="descriptionHtml" id="descriptionHtml" class="form-control" rows="5"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveProject" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Client Modal -->
    @if($showClientModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adaugă Client Nou</h5>
                        <button type="button" wire:click="closeClientModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveNewClient">
                            <div class="mb-3">
                                <label for="newClientFirstName" class="form-label">Prenume <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newClientFirstName" class="form-control @error('newClientFirstName') is-invalid @enderror" id="newClientFirstName">
                                @error('newClientFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="newClientLastName" class="form-label">Nume <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newClientLastName" class="form-control @error('newClientLastName') is-invalid @enderror" id="newClientLastName">
                                @error('newClientLastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="newClientEmail" class="form-label">Email</label>
                                <input type="email" wire:model="newClientEmail" class="form-control @error('newClientEmail') is-invalid @enderror" id="newClientEmail">
                                @error('newClientEmail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="newClientPhone" class="form-label">Telefon</label>
                                <input type="text" wire:model="newClientPhone" class="form-control @error('newClientPhone') is-invalid @enderror" id="newClientPhone">
                                @error('newClientPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeClientModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveNewClient" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Initialize Summernote for description
    let summernoteInitialized = false;

    function initializeSummernote() {
        if (!summernoteInitialized && $('#descriptionHtml').length) {
            $('#descriptionHtml').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents, $editable) {
                        @this.set('descriptionHtml', contents);
                    }
                }
            });
            summernoteInitialized = true;
        }
    }

    function destroySummernote() {
        if ($('#descriptionHtml').length && $('#descriptionHtml').summernote('code') !== undefined) {
            $('#descriptionHtml').summernote('destroy');
            summernoteInitialized = false;
        }
    }

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            if (document.querySelector('.modal.show')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 300);
            } else {
                destroySummernote();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const observer = new MutationObserver(function(mutations) {
            if (document.querySelector('.modal.show') && document.getElementById('descriptionHtml')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 200);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
</script>
@endpush

