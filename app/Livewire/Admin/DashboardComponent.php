<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin\Client;
use App\Models\Admin\Service;

class DashboardComponent extends Component
{
    public function render()
    {
        $totalClients = Client::count();
        $totalLeads = Client::where('type', 'lead')->count();
        $totalCustomers = Client::where('type', 'customer')->count();
        $totalServices = Service::count();
        $lastClients = Client::orderBy('created_at', 'desc')->limit(5)->get();
        
        return view('livewire.admin.dashboard-component', [
            'totalClients' => $totalClients,
            'totalLeads' => $totalLeads,
            'totalCustomers' => $totalCustomers,
            'totalServices' => $totalServices,
            'lastClients' => $lastClients,
        ]);
    }
}
