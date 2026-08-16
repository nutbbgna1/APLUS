<?php
// Assume this is included by index.php or similar router, so session is started and header/footer are handled.
// If it's a standalone page, we would need require_once headers etc, but typical structure for this project is ?page=...
?>

<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="page-title">My Class Schedule</h1>
        <p class="text-muted">View your upcoming live classes and study sessions.</p>
    </div>
</div>

<div class="card" style="padding: 24px; border-radius: 12px; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    <div id="student-calendar"></div>
</div>

<!-- Event Details Modal -->
<div id="eventDetailsModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 1000; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#fff; padding:24px; border-radius:12px; width:450px; max-width:90%; position:relative;">
        <button onclick="closeDetailsModal()" style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:1.5rem; cursor:pointer; color:#6B7280;">&times;</button>
        
        <h3 id="modalTitle" style="margin-bottom:15px; color:#111827;"></h3>
        
        <div style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-book" style="color:var(--primary);"></i>
            <span id="modalCourse" style="font-weight:500;"></span>
        </div>
        
        <div style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-clock" style="color:var(--primary);"></i>
            <span id="modalTime" style="color:#4B5563;"></span>
        </div>

        <div style="margin-bottom:15px;">
            <p style="font-weight:600; margin-bottom:5px; color:#374151;">Notes / Instructions:</p>
            <div id="modalNotes" style="color:#4B5563; background:#F3F4F6; padding:10px; border-radius:6px; min-height:50px;"></div>
        </div>

        <div style="text-align:right; margin-top:20px;">
            <button onclick="closeDetailsModal()" class="btn btn-primary">Close</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('student-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 650,
        events: 'api/student_calendar_api.php?start=start&end=end', // Simple API fetch
        eventClick: function(info) {
            // Show details
            document.getElementById('modalTitle').innerText = info.event.title;
            document.getElementById('modalCourse').innerText = info.event.extendedProps.course_name || 'General Session';
            
            let startStr = info.event.start.toLocaleString();
            let endStr = info.event.end ? ' - ' + info.event.end.toLocaleString() : '';
            document.getElementById('modalTime').innerText = startStr + endStr;
            
            document.getElementById('modalNotes').innerText = info.event.extendedProps.notes || 'No notes provided.';
            
            document.getElementById('eventDetailsModal').style.display = 'flex';
        }
    });
    calendar.render();
});

function closeDetailsModal() {
    document.getElementById('eventDetailsModal').style.display = 'none';
}
</script>
