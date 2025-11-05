<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Oferte</h2>
            <a href="{{ route('admin.proposals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Ofertă Nouă
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Filtre și Căutare -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după titlu, număr sau tag-uri...">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">Toate Status-urile</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Trimise</option>
                            <option value="accepted">Acceptate</option>
                            <option value="rejected">Respinse</option>
                            <option value="expired">Expirate</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="clientFilter" class="form-select">
                            <option value="">Toți Clienții</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tab-uri Status -->
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === '' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', '')" 
                           href="javascript:void(0)">
                            Toate ({{ $statusCounts['all'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'draft' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', 'draft')" 
                           href="javascript:void(0)">
                            Draft ({{ $statusCounts['draft'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'sent' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', 'sent')" 
                           href="javascript:void(0)">
                            Trimise ({{ $statusCounts['sent'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'accepted' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', 'accepted')" 
                           href="javascript:void(0)">
                            Acceptate ({{ $statusCounts['accepted'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', 'rejected')" 
                           href="javascript:void(0)">
                            Respinse ({{ $statusCounts['rejected'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'expired' ? 'active' : '' }}" 
                           wire:click="$set('statusFilter', 'expired')" 
                           href="javascript:void(0)">
                            Expirate ({{ $statusCounts['expired'] }})
                        </a>
                    </li>
                </ul>

                <!-- Tabel Proposals -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Număr</th>
                                <th>Titlu</th>
                                <th>Client</th>
                                <th>Dată</th>
                                <th>Valabil până</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proposals as $proposal)
                                <tr>
                                    <td><strong>{{ $proposal->proposal_number }}</strong></td>
                                    <td>{{ $proposal->title }}</td>
                                    <td>
                                        @if($proposal->client)
                                            {{ $proposal->client->first_name }} {{ $proposal->client->last_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $proposal->proposal_date->format('d.m.Y') }}</td>
                                    <td>
                                        <span class="{{ $proposal->valid_until < now() ? 'text-danger' : '' }}">
                                            {{ $proposal->valid_until->format('d.m.Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($proposal->currency)
                                            {{ number_format($proposal->total, 2) }} {{ $proposal->currency->symbol }}
                                        @else
                                            {{ number_format($proposal->total, 2) }}
                                        @endif
                                    </td>
                                    <td>
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
                                        <span class="badge bg-{{ $statusColors[$proposal->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$proposal->status] ?? $proposal->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.proposals.view', $proposal->id) }}" class="btn btn-sm btn-info" title="Vizualizează">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.proposals.pdf', $proposal->id) }}?preview=1" class="btn btn-sm btn-primary" target="_blank" title="Preview">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.proposals.pdf', $proposal->id) }}" class="btn btn-sm btn-secondary" target="_blank" title="Export PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nu există oferte.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $proposals->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

