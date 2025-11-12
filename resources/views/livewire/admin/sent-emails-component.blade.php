<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Log Email-uri Trimise</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Caută după subiect, destinatar, expeditor...">
                            </div>
                            <div class="col-md-3">
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">Toate statusurile</option>
                                    <option value="sent">Trimis</option>
                                    <option value="failed">Eșuat</option>
                                    <option value="pending">În așteptare</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" wire:model.live.debounce.300ms="toFilter" class="form-control" placeholder="Filtrează după destinatar...">
                            </div>
                            <div class="col-md-2">
                                <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('toFilter', '')" class="btn btn-secondary w-100">Resetează</button>
                            </div>
                        </div>

                        <!-- Emails Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Destinatar</th>
                                        <th>Expeditor</th>
                                        <th>Subiect</th>
                                        <th>Status</th>
                                        <th>Utilizator</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($emails as $email)
                                        <tr>
                                            <td>{{ $email->sent_at ? $email->sent_at->format('d.m.Y H:i') : $email->created_at->format('d.m.Y H:i') }}</td>
                                            <td>{{ $email->to }}</td>
                                            <td>
                                                @if($email->from_name)
                                                    {{ $email->from_name }}<br>
                                                    <small class="text-muted">{{ $email->from }}</small>
                                                @else
                                                    {{ $email->from }}
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($email->subject, 50) }}</td>
                                            <td>
                                                @if($email->status === 'sent')
                                                    <span class="badge bg-success">Trimis</span>
                                                @elseif($email->status === 'failed')
                                                    <span class="badge bg-danger">Eșuat</span>
                                                @else
                                                    <span class="badge bg-warning">În așteptare</span>
                                                @endif
                                            </td>
                                            <td>{{ $email->user ? $email->user->name : 'Sistem' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" wire:click="showEmail({{ $email->id }})" data-bs-toggle="modal" data-bs-target="#emailModal">
                                                    <i class="bi bi-eye"></i> Vezi
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Nu există email-uri logate.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $emails->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Detalii Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($emailModalId)
                        @php
                            $emailForModal = \App\Models\SentEmail::with('user')->find($emailModalId);
                        @endphp
                        @if($emailForModal)
                            <div class="mb-3">
                                <strong>Destinatar:</strong> {{ $emailForModal->to }}
                            </div>
                            <div class="mb-3">
                                <strong>Expeditor:</strong> 
                                @if($emailForModal->from_name)
                                    {{ $emailForModal->from_name }} &lt;{{ $emailForModal->from }}&gt;
                                @else
                                    {{ $emailForModal->from }}
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Subiect:</strong> {{ $emailForModal->subject }}
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> 
                                @if($emailForModal->status === 'sent')
                                    <span class="badge bg-success">Trimis</span>
                                @elseif($emailForModal->status === 'failed')
                                    <span class="badge bg-danger">Eșuat</span>
                                @else
                                    <span class="badge bg-warning">În așteptare</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Data trimiterii:</strong> {{ $emailForModal->sent_at ? $emailForModal->sent_at->format('d.m.Y H:i') : $emailForModal->created_at->format('d.m.Y H:i') }}
                            </div>
                            @if($emailForModal->user)
                                <div class="mb-3">
                                    <strong>Trimis de:</strong> {{ $emailForModal->user->name }}
                                </div>
                            @endif
                            @if($emailForModal->related_type && $emailForModal->related_id)
                                <div class="mb-3">
                                    <strong>Relaționat cu:</strong> {{ $emailForModal->related_type }} #{{ $emailForModal->related_id }}
                                </div>
                            @endif
                            @if($emailForModal->attachments)
                                <div class="mb-3">
                                    <strong>Atașamente:</strong>
                                    <ul>
                                        @foreach($emailForModal->attachments as $attachment)
                                            <li>{{ $attachment['filename'] }} ({{ number_format($attachment['size'] / 1024, 2) }} KB)</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="mb-3">
                                <strong>Conținut:</strong>
                                <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                                    @if($emailForModal->body_html)
                                        {!! $emailForModal->body_html !!}
                                    @elseif($emailForModal->body_text)
                                        <pre>{{ $emailForModal->body_text }}</pre>
                                    @else
                                        <em>Nu există conținut disponibil.</em>
                                    @endif
                                </div>
                            </div>
                            @if($emailForModal->error_message)
                                <div class="mb-3">
                                    <strong>Eroare:</strong>
                                    <div class="alert alert-danger">{{ $emailForModal->error_message }}</div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted">Email-ul nu a fost găsit.</p>
                        @endif
                    @else
                        <p class="text-muted">Selectează un email pentru a vedea detaliile.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                </div>
            </div>
        </div>
    </div>

</div>

