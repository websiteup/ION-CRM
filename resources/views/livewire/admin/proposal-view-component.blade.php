<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>{{ $isCreating ? 'Ofertă Nouă' : ($proposal->proposal_number ?? 'Editează Ofertă') }}</h2>
            <a href="{{ route('admin.proposals.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Înapoi
            </a>
        </div>

        <form wire:submit.prevent="save">
            <div class="row">
                <!-- Left Column - Form -->
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Informații Ofertă</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">Titlu *</label>
                                    <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" id="title">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="client_id" class="form-label">Client *</label>
                                    <select wire:model="client_id" class="form-select @error('client_id') is-invalid @enderror" id="client_id">
                                        <option value="">Selectează client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="template_id" class="form-label">Template</label>
                                    <select wire:model="template_id" class="form-select @error('template_id') is-invalid @enderror" id="template_id">
                                        <option value="">Fără template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }} @if($template->is_default)(Default)@endif</option>
                                        @endforeach
                                    </select>
                                    @error('template_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @if(!$isCreating)
                                    <div class="col-md-6 mb-3">
                                        <label for="proposal_number" class="form-label">Număr Ofertă</label>
                                        <input type="text" wire:model="proposal_number" class="form-control" id="proposal_number" readonly>
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <label for="proposal_date" class="form-label">Data Ofertei *</label>
                                    <input type="date" wire:model="proposal_date" class="form-control @error('proposal_date') is-invalid @enderror" id="proposal_date">
                                    @error('proposal_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="valid_until" class="form-label">Valabil până la *</label>
                                    <input type="date" wire:model="valid_until" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until">
                                    @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="currency_id" class="form-label">Valută</label>
                                    <select wire:model="currency_id" class="form-select @error('currency_id') is-invalid @enderror" id="currency_id">
                                        <option value="">Selectează valută</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) @if($currency->is_default)(Default)@endif</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror" id="status">
                                        <option value="draft">Draft</option>
                                        <option value="sent">Trimisă</option>
                                        <option value="accepted">Acceptată</option>
                                        <option value="rejected">Respină</option>
                                        <option value="expired">Expirată</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="tags" class="form-label">Tag-uri (separate prin virgulă)</label>
                                    <input type="text" wire:model="tags" class="form-control" id="tags" placeholder="tag1, tag2, tag3">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Note Interne</label>
                                    <textarea wire:model="notes" class="form-control" id="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Management -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Servicii / Items</h5>
                            <button type="button" wire:click="openItemModal" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Adaugă Item
                            </button>
                        </div>
                        <div class="card-body">
                            @if(count($items) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px;">#</th>
                                                <th>Categorie</th>
                                                <th>Subcategorie</th>
                                                <th>Descriere</th>
                                                <th style="width: 100px;">Cantitate</th>
                                                <th style="width: 120px;">Preț Unit.</th>
                                                <th style="width: 80px;">Tax %</th>
                                                <th style="width: 120px;">Total</th>
                                                <th style="width: 100px;">Acțiuni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupedItems as $category => $subcategories)
                                                @php
                                                    $categoryTotal = 0;
                                                    foreach ($subcategories as $subcatItems) {
                                                        foreach ($subcatItems as $item) {
                                                            $categoryTotal += $item['total'];
                                                        }
                                                    }
                                                @endphp
                                                <tr class="table-light">
                                                    <td colspan="8"><strong>{{ $category }}</strong></td>
                                                    <td class="text-end"><strong>{{ number_format($categoryTotal, 2) }}</strong></td>
                                                </tr>
                                                @foreach($subcategories as $subcategory => $subcatItems)
                                                    @if($subcategory !== 'General')
                                                        <tr class="table-secondary">
                                                            <td colspan="2"></td>
                                                            <td colspan="6"><em>{{ $subcategory }}</em></td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                    @foreach($subcatItems as $itemIndex => $item)
                                                        @php
                                                            $actualIndex = $item['_index'] ?? $itemIndex;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $itemIndex + 1 }}</td>
                                                            <td>{{ $item['category'] ?: '-' }}</td>
                                                            <td>{{ $item['subcategory'] ?: '-' }}</td>
                                                            <td>{{ $item['description'] }}</td>
                                                            <td>{{ number_format($item['quantity'], 2) }}</td>
                                                            <td>{{ number_format($item['unit_price'], 2) }}</td>
                                                            <td>{{ number_format($item['tax_rate'], 2) }}%</td>
                                                            <td class="text-end">{{ number_format($item['total'], 2) }}</td>
                                                            <td>
                                                                <button type="button" wire:click="openItemModal({{ $actualIndex }})" class="btn btn-sm btn-info">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button type="button" wire:click="deleteItem({{ $actualIndex }})" 
                                                                        wire:confirm="Ești sigur că vrei să ștergi acest item?" 
                                                                        class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="7" class="text-end"><strong>Subtotal:</strong></td>
                                                <td class="text-end"><strong>{{ number_format($subtotal, 2) }}</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-end"><strong>Taxe:</strong></td>
                                                <td class="text-end"><strong>{{ number_format($taxTotal, 2) }}</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="7" class="text-end"><strong>TOTAL:</strong></td>
                                                <td class="text-end"><strong>{{ number_format($total, 2) }}</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Nu există items. Adaugă cel puțin un item pentru a salva oferta.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvează Oferta
                            </button>
                            @if(!$isCreating && $proposalId)
                                <a href="{{ route('admin.proposals.pdf', $proposalId) }}?preview=1" class="btn btn-info" target="_blank">
                                    <i class="bi bi-eye"></i> Preview
                                </a>
                                <a href="{{ route('admin.proposals.pdf', $proposalId) }}" class="btn btn-secondary" target="_blank">
                                    <i class="bi bi-file-pdf"></i> Export PDF
                                </a>
                                @if($status === 'draft')
                                    <button type="button" class="btn btn-success" wire:click="sendEmail">
                                        <i class="bi bi-envelope"></i> Trimite Email
                                    </button>
                                @endif
                                @if(in_array($status, ['sent', 'accepted', 'rejected']))
                                    <button type="button" class="btn btn-warning" wire:click="sendEmail" wire:confirm="Ești sigur că vrei să retrimiți această ofertă?">
                                        <i class="bi bi-envelope-arrow-repeat"></i> Retrimite Email
                                    </button>
                                @endif
                                @if($status === 'sent')
                                    <button type="button" class="btn btn-success" wire:click="acceptProposal" wire:confirm="Ești sigur că vrei să accepti această ofertă?">
                                        <i class="bi bi-check-circle"></i> Acceptă
                                    </button>
                                    <button type="button" class="btn btn-danger" wire:click="rejectProposal" wire:confirm="Ești sigur că vrei să respingi această ofertă?">
                                        <i class="bi bi-x-circle"></i> Respinge
                                    </button>
                                @endif
                                <button type="button" class="btn btn-info" wire:click="duplicateProposal">
                                    <i class="bi bi-files"></i> Duplică
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Sumar</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">{{ number_format($subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Taxe:</strong></td>
                                    <td class="text-end">{{ number_format($taxTotal, 2) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>TOTAL:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Status</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'sent' => 'info',
                                    'accepted' => 'success',
                                    'rejected' => 'danger',
                                    'expired' => 'warning'
                                ];
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'sent' => 'Trimisă',
                                    'accepted' => 'Acceptată',
                                    'rejected' => 'Respină',
                                    'expired' => 'Expirată'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }} fs-6">
                                {{ $statusLabels[$status] ?? $status }}
                            </span>
                            @if($status === 'sent' && !$isCreating && $proposalId)
                                @php
                                    $proposal = \App\Models\Proposal::find($proposalId);
                                    $daysUntilExpiry = $proposal ? now()->diffInDays($proposal->valid_until, false) : 0;
                                @endphp
                                @if($daysUntilExpiry < 0)
                                    <p class="text-danger mt-2 mb-0">
                                        <small><i class="bi bi-exclamation-triangle"></i> Expirată acum {{ abs($daysUntilExpiry) }} zile</small>
                                    </p>
                                @elseif($daysUntilExpiry <= 7)
                                    <p class="text-warning mt-2 mb-0">
                                        <small><i class="bi bi-clock"></i> Expiră în {{ $daysUntilExpiry }} zile</small>
                                    </p>
                                @else
                                    <p class="text-muted mt-2 mb-0">
                                        <small><i class="bi bi-calendar-check"></i> Expiră în {{ $daysUntilExpiry }} zile</small>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if(!$isCreating && $proposalId && isset($proposal))
                        @php
                            $history = $proposal->history()->with('user')->get();
                        @endphp
                        @if($history->count() > 0)
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Istoric Ofertă</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($history as $event)
                                                <li class="mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <div class="rounded-circle bg-{{ $event->color }} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="bi {{ $event->icon }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">{{ $event->title }}</h6>
                                                            <p class="mb-1 text-muted small">{{ $event->description }}</p>
                                                            @if($event->changes)
                                                                <div class="mb-2">
                                                                    @foreach($event->changes as $key => $value)
                                                                        <small class="text-muted d-block">
                                                                            <strong>{{ $key }}:</strong> {{ $value }}
                                                                        </small>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <div class="d-flex align-items-center">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-calendar3"></i> {{ $event->event_date->format('d.m.Y H:i') }}
                                                                </small>
                                                                @if($event->user)
                                                                    <small class="text-muted ms-3">
                                                                        <i class="bi bi-person"></i> {{ $event->user->name }}
                                                                    </small>
                                                                @else
                                                                    <small class="text-muted ms-3">
                                                                        <i class="bi bi-gear"></i> Sistem
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(!$loop->last)
                                                        <div class="ms-5 ps-2" style="border-left: 2px solid #e0e0e0; height: 20px; margin-left: 20px;"></div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Item Modal -->
    @if($showItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingItemIndex !== null ? 'Editează Item' : 'Adaugă Item' }}</h5>
                        <button type="button" wire:click="closeItemModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="itemServiceId" class="form-label">Serviciu (opțional)</label>
                                <select wire:model.live="itemServiceId" class="form-select" id="itemServiceId">
                                    <option value="">Selectează serviciu sau adaugă manual</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} - {{ number_format($service->unit_price, 2) }} {{ $service->unit_type }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selectează un serviciu pentru a preîncărca automat descrierea, prețul și taxa</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="itemCategory" class="form-label">Categorie</label>
                                <input type="text" wire:model="itemCategory" class="form-control" id="itemCategory" placeholder="ex: Homepage - Überarbeitung">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="itemSubcategory" class="form-label">Subcategorie</label>
                                <input type="text" wire:model="itemSubcategory" class="form-control" id="itemSubcategory" placeholder="ex: Voreinstellungen">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="itemDescription" class="form-label">Descriere *</label>
                                <textarea wire:model="itemDescription" class="form-control @error('itemDescription') is-invalid @enderror" id="itemDescription" rows="3"></textarea>
                                @error('itemDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="itemQuantity" class="form-label">Cantitate *</label>
                                <input type="number" wire:model="itemQuantity" step="0.01" min="0.01" class="form-control @error('itemQuantity') is-invalid @enderror" id="itemQuantity">
                                @error('itemQuantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="itemUnitPrice" class="form-label">Preț Unitate *</label>
                                <input type="number" wire:model="itemUnitPrice" step="0.01" min="0" class="form-control @error('itemUnitPrice') is-invalid @enderror" id="itemUnitPrice">
                                @error('itemUnitPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="itemTaxRate" class="form-label">Tax %</label>
                                <input type="number" wire:model="itemTaxRate" step="0.01" min="0" max="100" class="form-control @error('itemTaxRate') is-invalid @enderror" id="itemTaxRate">
                                @error('itemTaxRate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeItemModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveItem" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

