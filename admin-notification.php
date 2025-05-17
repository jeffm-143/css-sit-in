<?php
// Get pending reservations count
$pending_count_query = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
$pending_count = $conn->query($pending_count_query)->fetch_assoc()['count'];
?>
<!-- Notification Bell -->
<li class="relative">
    <button id="notificationButton" class="text-white hover:text-yellow-400 transition-colors">
        <i class="fas fa-bell text-xl"></i>
        <?php if ($pending_count > 0): ?>
        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notificationCount">
            <?php echo $pending_count; ?>
        </span>
        <?php endif; ?>
    </button>
    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Pending Reservations</h3>
        </div>
        <div class="max-h-96 overflow-y-auto" id="notificationList">
            <?php
            // Get pending reservations
            $pending_notif_query = "
                SELECT r.*, u.FIRSTNAME, u.LASTNAME 
                FROM reservations r
                JOIN users u ON r.student_id = u.ID_NUMBER
                WHERE r.status = 'pending'
                ORDER BY r.created_at DESC
                LIMIT 5";
            $pending_notifications = $conn->query($pending_notif_query);
            
            if ($pending_notifications->num_rows > 0):
                while($notif = $pending_notifications->fetch_assoc()):
            ?>
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50" style="transition: opacity 0.3s ease-out;" data-reservation-id="<?php echo $notif['id']; ?>">
                    <p class="font-medium text-gray-800">
                        <?php echo htmlspecialchars($notif['FIRSTNAME'] . ' ' . $notif['LASTNAME']); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        Room <?php echo htmlspecialchars($notif['lab_room']); ?> - PC <?php echo htmlspecialchars($notif['pc_number']); ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                    </p>
                    
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="p-4 text-center text-gray-500">
                    No pending reservations
                </div>
            <?php endif; ?>
        </div>
    </div>
</li>

<?php if (!isset($notification_js_loaded)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && e.target !== notificationButton) {
                notificationDropdown.classList.add('hidden');
            }
        });
    }
});

function markNotificationAsRead(notificationId, button) {
    fetch('admin_dismiss_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notifItem = button.closest('div.p-4.border-b');
            notifItem.style.opacity = '0';
            setTimeout(() => {
                notifItem.remove();
                
                const notificationList = document.getElementById('notificationList');
                const remainingItems = notificationList.querySelectorAll('div.p-4.border-b');
                if (remainingItems.length === 0) {
                    notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No pending reservations</div>';
                }

                const counter = document.getElementById('notificationCount');
                if (counter) {
                    const currentCount = parseInt(counter.textContent);
                    if (currentCount > 1) {
                        counter.textContent = currentCount - 1;
                    } else {
                        counter.remove();
                    }
                }
            }, 300);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
<?php $notification_js_loaded = true; endif; ?>