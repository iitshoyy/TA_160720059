@extends('layouts.app')
@section('title', 'Reservations')
@section('page-title', 'Reservation Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Reservations</h1>
        <p>Manage online and offline table reservations</p>
    </div>
    <button class="btn btn-gold" onclick="openModal('addReservationModal')"><i class="fas fa-plus"></i> New Reservation</button>
</div>

<div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card info"><div class="stat-label">Today</div><div class="stat-value">{{ $todayCount ?? 0 }}</div></div>
    <div class="stat-card warning"><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingCount ?? 0 }}</div></div>
    <div class="stat-card success"><div class="stat-label">Confirmed</div><div class="stat-value">{{ $confirmedCount ?? 0 }}</div></div>
    <div class="stat-card danger"><div class="stat-label">Cancelled</div><div class="stat-value">{{ $cancelledCount ?? 0 }}</div></div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="switchTab('tabAll', this)">All Reservations</button>
    <button class="tab-btn" onclick="switchTab('tabToday', this)">Today</button>
    <button class="tab-btn" onclick="switchTab('tabUpcoming', this)">Upcoming</button>
</div>

<div class="tab-content active" id="tabAll">
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Guest Name</th>
                    <th>Phone</th>
                    <th>Date & Time</th>
                    <th>Guests</th>
                    <th>Table</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations ?? [] as $res)
                <tr>
                    <td class="text-gold">#{{ $res->id }}</td>
                    <td class="fw-500 text-cream">{{ $res->customer_name }}</td>
                    <td style="font-size:0.8rem;">{{ $res->phone }}</td>
                    <td>
                        <div class="fw-500">{{ \Carbon\Carbon::parse($res->reservation_date)->format('d M Y') }}</div>
                        <div style="font-size:0.78rem; color:var(--muted);">{{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }}</div>
                    </td>
                    <td style="text-align:center;">{{ $res->guests }} pax</td>
                    <td>{{ $res->table->name ?? 'Not assigned' }}</td>
                    <td>
                        <span class="status {{ $res->source === 'online' ? 'status-info' : 'status-completed' }}">
                            {{ ucfirst($res->source ?? 'offline') }}
                        </span>
                    </td>
                    <td><span class="status status-{{ $res->status }}">{{ ucfirst($res->status) }}</span></td>
                    <td style="font-size:0.78rem; color:var(--muted); max-width:150px;">{{ Str::limit($res->notes, 40) }}</td>
                    <td>
                        <div style="display:flex; gap:5px; flex-wrap:wrap;">
                            @if($res->status === 'pending')
                            <form method="POST" action="{{ route('reservations.update-status', $res->id) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                            </form>
                            @endif
                            @if(in_array($res->status, ['pending', 'confirmed']))
                            <form method="POST" action="{{ route('reservations.update-status', $res->id) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                            </form>
                            @endif
                            <button class="btn btn-outline btn-sm" onclick="openEditReservation({{ json_encode($res) }})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="10" icon="fas fa-calendar-xmark" message="No reservations found" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($reservations) && method_exists($reservations, 'hasPages') && $reservations->hasPages())
    <div style="margin-top:16px;">{{ $reservations->links() }}</div>
    @endif
</div>
</div>

<div class="tab-content" id="tabToday">
<div class="card">
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:14px;">
        @forelse($todayReservations ?? [] as $res)
        <div style="background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:16px; border-left: 3px solid {{ $res->status === 'confirmed' ? 'var(--success)' : ($res->status === 'pending' ? 'var(--warning)' : 'var(--danger)') }};">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <div class="fw-600 text-cream">{{ $res->customer_name }}</div>
                    <div style="font-size:0.78rem; color:var(--muted);">{{ $res->phone }}</div>
                </div>
                <span class="status status-{{ $res->status }}">{{ ucfirst($res->status) }}</span>
            </div>
            <div style="margin-top:12px; display:flex; gap:16px; font-size:0.82rem;">
                <span><i class="fas fa-clock" class="text-gold"></i> {{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }}</span>
                <span><i class="fas fa-users" class="text-gold"></i> {{ $res->guests }} pax</span>
                <span><i class="fas fa-chair" class="text-gold"></i> {{ $res->table->name ?? 'TBA' }}</span>
            </div>
            @if($res->notes)
            <div style="margin-top:8px; font-size:0.78rem; color:var(--muted); font-style:italic;">{{ $res->notes }}</div>
            @endif
        </div>
        @empty
        <div style="color:var(--muted); text-align:center; padding:40px; grid-column:1/-1;">No reservations today</div>
        @endforelse
    </div>
</div>
</div>

<div class="tab-content" id="tabUpcoming">
<div class="card">
    <p style="color:var(--muted); font-size:0.8rem; margin-bottom:12px;">Next 7 days — pending and confirmed only.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Date</th><th>Time</th><th>Guest</th><th>Phone</th><th>Pax</th><th>Table</th><th>Status</th><th>Notes</th></tr>
            </thead>
            <tbody>
            @forelse($upcomingReservations ?? [] as $res)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($res->reservation_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }}</td>
                    <td class="fw-500 text-cream">{{ $res->customer_name }}</td>
                    <td style="font-size:0.8rem;">{{ $res->phone }}</td>
                    <td style="text-align:center;">{{ $res->guests }}</td>
                    <td>{{ $res->table->name ?? 'Not assigned' }}</td>
                    <td><span class="status status-{{ $res->status }}">{{ ucfirst($res->status) }}</span></td>
                    <td style="font-size:0.78rem; color:var(--muted); max-width:200px;">{{ \Illuminate\Support\Str::limit($res->notes, 50) }}</td>
                </tr>
            @empty
                <x-empty-state colspan="8" icon="fas fa-calendar" message="No upcoming reservations in the next 7 days" />
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Edit Reservation Modal -->
<div class="modal-overlay" id="editReservationModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit Reservation <span id="erId" style="color:var(--gold);"></span></div>
            <button class="modal-close" onclick="closeModal('editReservationModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editReservationForm">
        @csrf @method('PUT')
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Guest Name</label>
                    <input type="text" name="customer_name" id="erName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="erPhone" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="reservation_date" id="erDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" name="reservation_time" id="erTime" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Guests</label>
                    <input type="number" name="guests" id="erGuests" class="form-control" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Table</label>
                    <select name="table_id" id="erTable" class="form-control">
                        <option value="">Auto-assign</option>
                        @foreach($tables ?? [] as $table)
                        <option value="{{ $table->id }}">{{ $table->name }} ({{ $table->capacity }} pax)</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="erEmail" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="erNotes" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editReservationModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Changes</button>
        </div>
        </form>
    </div>
</div>

<!-- Add Reservation Modal -->
<div class="modal-overlay" id="addReservationModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">New Reservation</div>
            <button class="modal-close" onclick="closeModal('addReservationModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('reservations.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Guest Name</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="08xx-xxxx-xxxx">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="reservation_date" class="form-control" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" name="reservation_time" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests" class="form-control" required min="1" placeholder="2">
                </div>
                <div class="form-group">
                    <label class="form-label">Table</label>
                    <select name="table_id" class="form-control">
                        <option value="">Auto-assign</option>
                        @foreach($tables ?? [] as $table)
                        <option value="{{ $table->id }}">{{ $table->name }} ({{ $table->capacity }} pax)</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Special Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Birthday celebration, Allergy info, Special requests..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Source</label>
                <select name="source" class="form-control">
                    <option value="offline">Walk-in / Phone</option>
                    <option value="online">Online</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addReservationModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-calendar-plus"></i> Create Reservation</button>
        </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditReservation(res) {
    document.getElementById('editReservationForm').action = '/reservations/' + res.id;
    document.getElementById('erId').textContent  = '#' + res.id;
    document.getElementById('erName').value      = res.customer_name ?? '';
    document.getElementById('erPhone').value     = res.phone ?? '';
    document.getElementById('erDate').value      = (res.reservation_date ?? '').substring(0, 10);
    document.getElementById('erTime').value      = (res.reservation_time ?? '').substring(0, 5);
    document.getElementById('erGuests').value    = res.guests ?? 1;
    document.getElementById('erTable').value     = res.table_id ?? '';
    document.getElementById('erEmail').value     = res.email ?? '';
    document.getElementById('erNotes').value     = res.notes ?? '';
    openModal('editReservationModal');
}
</script>
@endpush
