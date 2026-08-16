<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

$courses = [];
try {
    if ($db) {
        $stmt = $db->query("SELECT id, title as name FROM courses ORDER BY id DESC");
        if ($stmt) {
            $courses = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading courses: " . $e->getMessage() . ". Did you run the migration?</div>";
}
?>

<div class="header-action">
    <h2>Teaching Calendar</h2>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fa-solid fa-plus"></i> Add Schedule
    </button>
</div>

<div class="card" style="margin-top: 20px; padding: 20px;">
    <div id="calendar"></div>
</div>

<!-- Add Event Modal -->
<div id="eventModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 1000; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#fff; padding:20px; border-radius:8px; width:500px; max-width:90%;">
        <h3>Add New Schedule</h3>
        <form id="eventForm" style="margin-top:15px;">
            <div class="form-group" style="margin-bottom:15px;">
                <label>Title</label>
                <input type="text" name="title" class="form-control" style="width:100%; padding:8px;" required>
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label>Course (Optional)</label>
                <select name="course_id" class="form-control" style="width:100%; padding:8px;">
                    <option value="">-- No Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label>Start Time</label>
                    <input type="datetime-local" name="start_datetime" class="form-control" style="width:100%; padding:8px;" required>
                </div>
                <div style="flex:1;">
                    <label>End Time</label>
                    <input type="datetime-local" name="end_datetime" class="form-control" style="width:100%; padding:8px;" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label>Assign Students</label>
                <select id="studentSelect" name="students[]" multiple class="form-control" style="width:100%; height:100px; padding:8px;">
                    <!-- Filled via JS -->
                </select>
                <small style="color:var(--text-muted);">Hold Ctrl/Cmd to select multiple students.</small>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label>Color</label>
                <input type="color" name="color" value="#4F46E5" style="width:100%; height:40px;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- FullCalendar Library -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let calendar;
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'api/calendar_api.php?action=get_events',
        eventClick: function(info) {
            if (confirm("Do you want to delete this event: " + info.event.title + "?")) {
                let formData = new FormData();
                formData.append('id', info.event.id);
                fetch('api/calendar_api.php?action=delete_event', {
                    method: 'POST',
                    body: formData
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        info.event.remove();
                    } else {
                        alert("Error: " + data.error);
                    }
                });
            }
        }
    });
    calendar.render();

    // Load Students
    fetch('api/calendar_api.php?action=get_students')
    .then(res => res.json())
    .then(data => {
        let select = document.getElementById('studentSelect');
        data.forEach(s => {
            let opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.fname + ' ' + s.lname + ' (' + s.username + ')';
            select.appendChild(opt);
        });
    });

    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        fetch('api/calendar_api.php?action=add_event', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            if (data.success) {
                closeModal();
                calendar.refetchEvents();
                this.reset();
            } else {
                alert("Error saving schedule");
            }
        });
    });
});

function openAddModal() {
    document.getElementById('eventModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('eventModal').style.display = 'none';
}
</script>
