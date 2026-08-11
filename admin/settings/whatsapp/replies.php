<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

$menuPath = "../../";
$currentPage = "settings";

require_once("../../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Ensure WhatsAppReplies table exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS WhatsAppReplies (
        ReplyID INT AUTO_INCREMENT PRIMARY KEY,
        MessageID VARCHAR(255) UNIQUE,
        SenderPhone VARCHAR(50) NOT NULL,
        SenderName VARCHAR(150) DEFAULT NULL,
        MessageType VARCHAR(50) NOT NULL DEFAULT 'text',
        MessageBody TEXT DEFAULT NULL,
        MediaID VARCHAR(255) DEFAULT NULL,
        MediaMimeType VARCHAR(100) DEFAULT NULL,
        LocalFilePath VARCHAR(255) DEFAULT NULL,
        RawPayload LONGTEXT DEFAULT NULL,
        Status VARCHAR(50) DEFAULT 'Unread',
        ReceivedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sender_phone (SenderPhone),
        INDEX idx_message_type (MessageType),
        INDEX idx_received_at (ReceivedAt)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    // Table exists or DB error
}

// Handle Mark as Read / Delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'mark_read') {
        $stmt = $conn->prepare("UPDATE WhatsAppReplies SET Status = 'Read' WHERE ReplyID = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = "Message marked as Read";
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM WhatsAppReplies WHERE ReplyID = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = "Message deleted successfully";
    }
    header("Location: replies.php");
    exit();
}

// Filters
$typeFilter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

$query = "SELECT * FROM WhatsAppReplies WHERE 1=1";
$params = [];

if ($typeFilter !== '') {
    $query .= " AND MessageType = ?";
    $params[] = $typeFilter;
}

if ($search !== '') {
    $query .= " AND (SenderPhone LIKE ? OR SenderName LIKE ? OR MessageBody LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY ReceivedAt DESC LIMIT 100";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../../components/sidebar.php");
include("../../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received WhatsApp Customer Replies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .inbox-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 9px 14px;
            border: 1px solid var(--border-color, #e0e0e0);
            border-radius: 6px;
            font-size: 14px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .reply-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .reply-table th,
        .reply-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .reply-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .badge-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-text { background: #e3f2fd; color: #1976d2; }
        .badge-image { background: #e8f5e9; color: #2e7d32; }
        .badge-document { background: #fff3e0; color: #ef6c00; }
        .badge-audio { background: #f3e5f5; color: #7b1fa2; }
        .badge-video { background: #fbe9e7; color: #d84315; }
        .badge-other { background: #eceff1; color: #455a64; }

        .media-preview {
            max-width: 120px;
            max-height: 120px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #ddd;
            margin-top: 5px;
        }

        .status-unread {
            font-weight: bold;
            background-color: #fafbfc;
        }

        .action-btns a {
            margin-right: 8px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }

        .action-btns a:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h1 class="page-title" style="font-size: 24px; font-weight: 600; margin: 0;">
                    <i class="fab fa-whatsapp" style="color: #25D366; margin-right: 8px;"></i> Customer WhatsApp Replies
                </h1>
                <p style="font-size: 13px; color: #666; margin-top: 4px;">View incoming customer texts, images, and document replies stored via Webhook.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary" style="padding: 10px 15px; background: white; border: 1px solid #ccc; border-radius: 6px; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-cog"></i> API Settings
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" style="padding: 12px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="inbox-card">
            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Search phone, name, or message..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                <select name="type">
                    <option value="">All Message Types</option>
                    <option value="text" <?php echo $typeFilter === 'text' ? 'selected' : ''; ?>>Text</option>
                    <option value="image" <?php echo $typeFilter === 'image' ? 'selected' : ''; ?>>Image</option>
                    <option value="document" <?php echo $typeFilter === 'document' ? 'selected' : ''; ?>>Document</option>
                    <option value="audio" <?php echo $typeFilter === 'audio' ? 'selected' : ''; ?>>Audio / Voice Note</option>
                    <option value="video" <?php echo $typeFilter === 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
                <button type="submit" class="btn btn-primary" style="padding: 9px 18px; background: #3a7bd5; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-search"></i> Filter
                </button>
                <?php if ($search !== '' || $typeFilter !== ''): ?>
                    <a href="replies.php" style="padding: 9px 14px; background: #eee; color: #333; text-decoration: none; border-radius: 6px; font-size: 14px;">Clear</a>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="reply-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Type</th>
                            <th>Message / Media Content</th>
                            <th>Received At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($replies)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: #888;">
                                    <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                                    No incoming WhatsApp customer replies stored yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($replies as $row): ?>
                                <tr class="<?php echo $row['Status'] === 'Unread' ? 'status-unread' : ''; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['SenderName'] ?: 'Unknown'); ?></strong><br>
                                        <small style="color: #666;">+<?php echo htmlspecialchars($row['SenderPhone']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $type = strtolower($row['MessageType']);
                                        $badgeClass = isset(['text' => 'badge-text', 'image' => 'badge-image', 'document' => 'badge-document', 'audio' => 'badge-audio', 'video' => 'badge-video'][$type]) ? ['text' => 'badge-text', 'image' => 'badge-image', 'document' => 'badge-document', 'audio' => 'badge-audio', 'video' => 'badge-video'][$type] : 'badge-other';
                                        ?>
                                        <span class="badge-type <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($type); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['MessageBody'])): ?>
                                            <div><?php echo nl2br(htmlspecialchars($row['MessageBody'])); ?></div>
                                        <?php endif; ?>

                                        <?php if (!empty($row['LocalFilePath'])): ?>
                                            <?php $filePath = '../../../' . ltrim($row['LocalFilePath'], '/'); ?>
                                            <?php if (in_array($type, ['image', 'sticker'])): ?>
                                                <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">
                                                    <img src="<?php echo htmlspecialchars($filePath); ?>" class="media-preview" alt="User image reply">
                                                </a>
                                            <?php elseif ($type === 'audio'): ?>
                                                <div style="margin-top: 8px;">
                                                    <audio controls style="max-width: 250px; height: 35px;">
                                                        <source src="<?php echo htmlspecialchars($filePath); ?>" type="<?php echo htmlspecialchars($row['MediaMimeType'] ?: 'audio/ogg'); ?>">
                                                    </audio>
                                                </div>
                                            <?php elseif ($type === 'video'): ?>
                                                <div style="margin-top: 8px;">
                                                    <video controls style="max-width: 250px; max-height: 150px; border-radius: 6px;">
                                                        <source src="<?php echo htmlspecialchars($filePath); ?>" type="<?php echo htmlspecialchars($row['MediaMimeType'] ?: 'video/mp4'); ?>">
                                                    </video>
                                                </div>
                                            <?php else: ?>
                                                <div style="margin-top: 6px;">
                                                    <a href="<?php echo htmlspecialchars($filePath); ?>" download style="color: #1976d2; font-weight: 500;">
                                                        <i class="fas fa-download"></i> Download Attached File
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y g:i A', strtotime($row['ReceivedAt'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($row['Status'] === 'Unread'): ?>
                                            <span style="color: #e53935; font-weight: 600;"><i class="fas fa-envelope"></i> Unread</span>
                                        <?php else: ?>
                                            <span style="color: #43a047;"><i class="fas fa-check-double"></i> Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-btns">
                                        <?php if ($row['Status'] === 'Unread'): ?>
                                            <a href="replies.php?action=mark_read&id=<?php echo $row['ReplyID']; ?>" title="Mark as Read">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="replies.php?action=delete&id=<?php echo $row['ReplyID']; ?>" onclick="return confirm('Delete this message?')" title="Delete Message" style="color: #c62828;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
