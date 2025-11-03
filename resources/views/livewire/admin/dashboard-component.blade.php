<div>
    <div class="container-fluid py-4">
        <h2 class="mb-4">Dashboard</h2>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Clienți</h6>
                                <h3 class="card-title mb-0">{{ $totalClients }}</h3>
                            </div>
                            <i class="bi bi-people fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Leads</h6>
                                <h3 class="card-title mb-0">{{ $totalLeads }}</h3>
                            </div>
                            <i class="bi bi-person-plus fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Clienți</h6>
                                <h3 class="card-title mb-0">{{ $totalCustomers }}</h3>
                            </div>
                            <i class="bi bi-person-check fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Servicii</h6>
                                <h3 class="card-title mb-0">{{ $totalServices }}</h3>
                            </div>
                            <i class="bi bi-briefcase fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ultimii 5 Clienți</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tip</th>
                                        <th>Nume</th>
                                        <th>Email</th>
                                        <th>Telefon</th>
                                        <th>Data Adăugării</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lastClients as $client)
                                        <tr>
                                            <td>{{ $client->id }}</td>
                                            <td>
                                                <span class="badge bg-{{ $client->type === 'customer' ? 'success' : 'warning' }}">
                                                    {{ $client->type === 'customer' ? 'Client' : 'Lead' }}
                                                </span>
                                            </td>
                                            <td>{{ $client->first_name }} {{ $client->last_name }}</td>
                                            <td>{{ $client->email ?? '-' }}</td>
                                            <td>{{ $client->phone ?? '-' }}</td>
                                            <td>{{ $client->created_at->format('d.m.Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Nu există clienți.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
