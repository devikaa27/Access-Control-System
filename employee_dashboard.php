<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Employee';
$today = date('Y-m-d');

// Fetch notifications
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC");
$notif_count = mysqli_num_rows($notif_query);

// Fetch attendance
$att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
$att_data = mysqli_fetch_assoc($att_today);

// Auto Check-In
if (!$att_data || empty($att_data['check_in'])) {
    $now = date('Y-m-d H:i:s');
    if ($att_data) {
        mysqli_query($conn, "UPDATE attendance SET check_in = '$now' WHERE user_id = $user_id AND date = '$today'");
    } else {
        mysqli_query($conn, "INSERT INTO attendance (user_id, date, check_in) VALUES ($user_id, '$today', '$now')");
    }
    $att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
    $att_data = mysqli_fetch_assoc($att_today);
}

// Auto Check-Out if after 6 PM
if (empty($att_data['check_out']) && date('H:i') >= '18:00') {
    $now = date('Y-m-d H:i:s');
    mysqli_query($conn, "UPDATE attendance SET check_out = '$now' WHERE user_id = $user_id AND date = '$today'");
    $att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
    $att_data = mysqli_fetch_assoc($att_today);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styledasboard2.css">
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .break-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        
        .break-section h3 {
            margin-bottom: 15px;
            color: #495057;
        }
        
        .break-section button {
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .break-section button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .break-section button:disabled {
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <h2>My Dashboard</h2>
        <nav>
            <ul>
                <li><a href="employee_dashboard.php" class="active">Home</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="settings.html">Settings</a></li>
                <li><a href="leave_form.php">Leave Form</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Header -->
        <div class="dashboard-header-bg">
            <header class="dashboard-topbar">
                <h1>Welcome back!</h1>
                <div class="topbar-profile">
                    <img src="images/5987424.png" alt="Profile">
                    <span><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>

            <!-- Stat Cards -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="icon" style="background-color:#2dce89;">✔️</div>
                    <div class="text">
                        <small>Check In</small>
                        <strong><?php echo isset($att_data['check_in']) ? date('h:i A', strtotime($att_data['check_in'])) : '--'; ?></strong>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon" style="background-color:#f5365c;">❌</div>
                    <div class="text">
                        <small>Check Out</small>
                        <strong><?php echo isset($att_data['check_out']) ? date('h:i A', strtotime($att_data['check_out'])) : '--'; ?></strong>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon" style="background-color:#11cdef;">📅</div>
                    <div class="text">
                        <small>Date</small>
                        <strong><?php echo $today; ?></strong>
                    </div>
                </div>

                <div class="stat-card notification-trigger">
                    <div class="icon" style="background-color:#8965e0;">🔔</div>
                    <div class="text">
                        <small>Notifications</small>
                        <strong><?php echo $notif_count; ?> New</strong>
                    </div>
                </div>
            </section>
        </div>

        <!-- Break Section -->
        <section class="break-section" style="margin-top: 30px;">
            <h3>🕒 Break Control</h3>
            <button id="start-break-btn" onclick="startBreak()" style="padding:10px 20px; background:#2dce89; color:white; border:none; border-radius:5px; margin-right:10px;">Start Break</button>
            <button id="end-break-btn" onclick="endBreak()" style="padding:10px 20px; background:#f5365c; color:white; border:none; border-radius:5px; margin-right:10px;" disabled>End Break</button>
            <div id="break-status" style="margin-top:10px; font-weight:bold; padding:10px; background:#f8f9fa; border-radius:5px;"></div>
        </section>

        <!-- Notification Panel -->
        <div class="card notification-panel" style="display: none;">
            <span class="notif-icon">🔔</span>
            <h4>Notifications</h4>
            <ul>
                <?php if ($notif_count > 0): ?>
                    <?php
                    mysqli_data_seek($notif_query, 0);
                    while ($notif = mysqli_fetch_assoc($notif_query)) { ?>
                        <li>
                            <?php echo htmlspecialchars($notif['message']); ?><br>
                            <small><?php echo date('d M Y, H:i', strtotime($notif['created_at'])); ?></small>
                        </li>
                    <?php } ?>
                <?php else: ?>
                    <li>No new notifications</li>
                <?php endif; ?>
            </ul>
            <form method="POST" action="mark_notifications.php">
                <button class="mark-read" type="submit">Mark all as read</button>
            </form>
        </div>
    </main>
</div>

<script>
    // Toggle notification panel
    document.querySelector('.notification-trigger').addEventListener('click', function () {
        const panel = document.querySelector('.notification-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });

    // Break system functionality
    let breakTimer = null;
    
    function updateBreakStatus() {
        fetch('get_break_status.php')
            .then(res => res.json())
            .then(data => {
                const statusDiv = document.getElementById('break-status');
                const startBtn = document.getElementById('start-break-btn');
                const endBtn = document.getElementById('end-break-btn');
                
                if (data.success && data.on_break) {
                    statusDiv.innerHTML = `🟡 ${data.message}`;
                    statusDiv.style.background = '#fff3cd';
                    statusDiv.style.color = '#856404';
                    startBtn.disabled = true;
                    startBtn.style.opacity = '0.5';
                    endBtn.disabled = false;
                    endBtn.style.opacity = '1';
                    
                    // Start live timer
                    if (!breakTimer) {
                        breakTimer = setInterval(updateBreakStatus, 30000); // Update every 30 seconds
                    }
                } else {
                    statusDiv.innerHTML = `🟢 ${data.message || 'Available for work'}`;
                    statusDiv.style.background = '#d4edda';
                    statusDiv.style.color = '#155724';
                    startBtn.disabled = false;
                    startBtn.style.opacity = '1';
                    endBtn.disabled = true;
                    endBtn.style.opacity = '0.5';
                    
                    // Stop timer
                    if (breakTimer) {
                        clearInterval(breakTimer);
                        breakTimer = null;
                    }
                }
            })
            .catch(err => {
                console.error('Error checking break status:', err);
                document.getElementById('break-status').innerHTML = '❌ Error loading break status';
            });
    }

    function startBreak() {
        const startBtn = document.getElementById('start-break-btn');
        startBtn.disabled = true;
        startBtn.innerText = 'Starting...';
        
        fetch('break_start.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBreakStatus();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                    startBtn.disabled = false;
                    startBtn.innerText = 'Start Break';
                }
            })
            .catch(err => {
                console.error('Error starting break:', err);
                showNotification('Failed to start break', 'error');
                startBtn.disabled = false;
                startBtn.innerText = 'Start Break';
            });
    }

    function endBreak() {
        const endBtn = document.getElementById('end-break-btn');
        endBtn.disabled = true;
        endBtn.innerText = 'Ending...';
        
        fetch('break_end.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBreakStatus();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                    endBtn.disabled = false;
                    endBtn.innerText = 'End Break';
                }
            })
            .catch(err => {
                console.error('Error ending break:', err);
                showNotification('Failed to end break', 'error');
                endBtn.disabled = false;
                endBtn.innerText = 'End Break';
            });
    }
    
    function showNotification(message, type) {
        // Create notification element
        const notif = document.createElement('div');
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
        `;
        notif.innerHTML = message;
        document.body.appendChild(notif);
        
        // Remove after 4 seconds
        setTimeout(() => {
            notif.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }
    
    // Initialize break status when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateBreakStatus();
    });
</script>
</body>
</html>
