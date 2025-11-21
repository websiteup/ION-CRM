<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-event"></i> Calendar</h2>
            <div class="d-flex gap-2 align-items-center">
                @if($isConnected)
                    <span class="text-success" title="Conectat la Google Calendar. Task-urile tale vor fi sincronizate automat.">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                    <form action="{{ route('admin.calendar.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-arrow-repeat"></i> Sincronizează cu Google Calendar
                        </button>
                    </form>
                    <form action="{{ route('admin.calendar.disconnect') }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Ești sigur că vrei să te deconectezi de la Google Calendar?')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-x-circle"></i> Deconectează
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.calendar.connect') }}" class="btn btn-primary">
                        <i class="bi bi-google"></i> Conectează Google Calendar
                    </a>
                @endif
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Detalii Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="taskModalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<style>
    #calendar {
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-event:hover {
        opacity: 0.8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/ro.js"></script>
<script>
    // Initialize calendar function
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            console.error('Calendar element not found!');
            return;
        }
        
        const tasks = @json($tasks);
        console.log('Tasks/Events loaded:', tasks);
        console.log('Number of events:', tasks.length);
        
        // Check if FullCalendar is loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar is not loaded!');
            calendarEl.innerHTML = '<div class="alert alert-danger">Eroare: FullCalendar nu s-a încărcat. Verifică conexiunea la internet.</div>';
            return;
        }
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ro',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: tasks,
            eventClick: function(info) {
                const event = info.event;
                const extendedProps = event.extendedProps;
                const isGoogleCalendar = extendedProps.source === 'google_calendar';
                
                const modalBody = document.getElementById('taskModalBody');
                const modalTitle = document.getElementById('taskModalLabel');
                
                if (isGoogleCalendar) {
                    modalTitle.textContent = 'Detalii Eveniment Google Calendar';
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <strong>Titlu:</strong><br>
                            <span>${event.title}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Data/Ora:</strong><br>
                            <span>${event.start ? new Date(event.start).toLocaleString('ro-RO') : 'N/A'}</span>
                            ${event.end && event.end !== event.start ? ` - ${new Date(event.end).toLocaleString('ro-RO')}` : ''}
                        </div>
                        ${extendedProps.location ? `
                        <div class="mb-3">
                            <strong>Locație:</strong><br>
                            <span>${extendedProps.location}</span>
                        </div>
                        ` : ''}
                        ${extendedProps.description ? `
                        <div class="mb-3">
                            <strong>Descriere:</strong><br>
                            <span>${extendedProps.description}</span>
                        </div>
                        ` : ''}
                        ${extendedProps.organizer ? `
                        <div class="mb-3">
                            <strong>Organizator:</strong><br>
                            <span>${extendedProps.organizer}</span>
                        </div>
                        ` : ''}
                        <div class="mt-3">
                            <a href="${event.url}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Deschide în Google Calendar
                            </a>
                        </div>
                    `;
                } else {
                    modalTitle.textContent = 'Detalii Task';
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <strong>Titlu:</strong><br>
                            <span>${event.title}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Proiect:</strong><br>
                            <span>${extendedProps.project || 'N/A'}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Prioritate:</strong><br>
                            <span class="badge bg-${getPriorityBadgeColor(extendedProps.priority)}">${extendedProps.priority || 'N/A'}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Atribuit la:</strong><br>
                            <span>${extendedProps.assigned_to || 'N/A'}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Data scadenței:</strong><br>
                            <span>${event.start ? new Date(event.start).toLocaleDateString('ro-RO') : 'N/A'}</span>
                        </div>
                        ${extendedProps.description ? `
                        <div class="mb-3">
                            <strong>Descriere:</strong><br>
                            <span>${extendedProps.description}</span>
                        </div>
                        ` : ''}
                        <div class="mt-3">
                            <a href="${event.url}" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-right"></i> Vezi Task
                            </a>
                        </div>
                    `;
                }
                
                const modal = new bootstrap.Modal(document.getElementById('taskModal'));
                modal.show();
            },
            eventDisplay: 'block',
            dayMaxEvents: true,
            moreLinkClick: 'popover',
        });
        
        calendar.render();
        console.log('Calendar rendered successfully');
        
        function getPriorityBadgeColor(priority) {
            const colors = {
                'urgent': 'danger',
                'high': 'warning',
                'medium': 'info',
                'low': 'success'
            };
            return colors[priority] || 'secondary';
        }
    }
    
    // Wait for both DOM and FullCalendar to be ready
    function waitForFullCalendar(callback, maxAttempts = 100) {
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            console.log('Checking for FullCalendar, attempt ' + attempts + '...');
            
            // Check multiple ways FullCalendar might be available
            if (typeof FullCalendar !== 'undefined' || typeof window.FullCalendar !== 'undefined') {
                clearInterval(checkInterval);
                console.log('FullCalendar found!');
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('FullCalendar failed to load after ' + maxAttempts + ' attempts');
                console.log('Available globals:', Object.keys(window).filter(k => k.toLowerCase().includes('calendar')));
                
                // Try to load from unpkg as last resort
                console.log('Attempting to load FullCalendar from unpkg...');
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/fullcalendar@6.1.10/index.global.min.js';
                script.onload = function() {
                    console.log('FullCalendar loaded from unpkg, initializing...');
                    setTimeout(callback, 100);
                };
                script.onerror = function() {
                    const calendarEl = document.getElementById('calendar');
                    if (calendarEl) {
                        calendarEl.innerHTML = '<div class="alert alert-danger">Eroare: FullCalendar nu s-a putut încărca. Verifică conexiunea la internet sau contactează administratorul.</div>';
                    }
                };
                document.head.appendChild(script);
            }
        }, 200);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, waiting for FullCalendar...');
            waitForFullCalendar(initializeCalendar);
        });
    } else {
        // DOM already loaded
        console.log('DOM already loaded, waiting for FullCalendar...');
        waitForFullCalendar(initializeCalendar);
    }
</script>
@endpush
