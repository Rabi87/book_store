<?php
session_start();
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}
// ------ معالجة طلبات POST ------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // إنشاء مجموعة جديدة
    if (isset($_POST['group_name'])) {
        $groupName = $_POST['group_name'];
        $ownerId = $_SESSION['user_id'];
        $uniqueCode = bin2hex(random_bytes(16));

        $stmt = $conn->prepare("INSERT INTO users_groups (group_name, owner_id, unique_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $groupName, $ownerId, $uniqueCode);
        $stmt->execute();

        $group_id = $stmt->insert_id;
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $group_id, $ownerId);
        $stmt->execute();

        $_SESSION['message'] = "تم إنشاء المجموعة! الرابط: " . BASE_URL . "Forum/join_group.php?code=" . $uniqueCode;
        header("Location: manage_groups.php");
        exit();
    }

    // طلب الانضمام إلى مجموعة
    if (isset($_POST['join_group'])) {
        $groupId = intval($_POST['group_id']);
        $userId = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO join_requests (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
        $_SESSION['message'] = 'تم إرسال طلب الانضمام!';
        header("Location: manage_groups.php");
        exit();
    }

    // مغادرة مجموعة
    if (isset($_POST['leave_group'])) {
        $groupId = intval($_POST['group_id']);
        $userId = $_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT owner_id FROM users_groups WHERE group_id = ?");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $ownerId = $stmt->get_result()->fetch_assoc()['owner_id'];

        if ($ownerId != $userId) {
            $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $groupId, $userId);
            $stmt->execute();
            $_SESSION['message'] = "تم مغادرة المجموعة بنجاح!";
        } else {
            $_SESSION['message'] = "لا يمكنك مغادرة المجموعة لأنك المالك!";
        }

        header("Location: manage_groups.php");
        exit();
    }
}

// ------ جلب البيانات ------
// جميع المجموعات
$stmt = $conn->prepare("SELECT group_id, group_name, owner_id, unique_code FROM users_groups");
$stmt->execute();
$allGroups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// مجموعات المستخدم الحالي
$userGroupsStmt = $conn->prepare("
    SELECT g.group_id 
    FROM group_members gm 
    JOIN users_groups g ON gm.group_id = g.group_id 
    WHERE gm.user_id = ?
");
$userGroupsStmt->bind_param("i", $_SESSION['user_id']);
$userGroupsStmt->execute();
$userGroups = $userGroupsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$userGroupIds = array_column($userGroups, 'group_id');

// الطلبات المعلقة
$pendingRequestsStmt = $conn->prepare("SELECT group_id FROM join_requests WHERE user_id = ? AND status = 'pending'");
$pendingRequestsStmt->bind_param("i", $_SESSION['user_id']);
$pendingRequestsStmt->execute();
$pendingRequests = $pendingRequestsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pendingGroupIds = array_column($pendingRequests, 'group_id');

require __DIR__ . '/../includes/header.php';
?>

<!-- عرض الرسائل -->
<?php if (isset($_SESSION['message'])): ?>
<div class="container mt-3">
    <div class="alert alert-info">
        <?= $_SESSION['message'] ?>
    </div>
</div>
<?php unset($_SESSION['message']); ?>
<?php endif; ?>

<!-- نموذج إنشاء المجموعة -->
<div class="container mt-4">
    <form method="POST" class="card p-3 mb-4">
        <div class="input-group">
            <input type="text" name="group_name" class="form-control" placeholder="اسم المجموعة" required>
            <button type="submit" class="btn btn-primary">إنشاء المجموعة</button>
        </div>
    </form>
</div>

<!-- جدول جميع المجموعات -->
<div class="container mt-4">
    <div class="notifications-scroll-container">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>اسم المجموعة</th>
                    <th>الإجراء</th>
                    <th>المحتويات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allGroups as $group): 
                $isMember = in_array($group['group_id'], $userGroupIds);
                $isPending = in_array($group['group_id'], $pendingGroupIds);
                $isOwner = ($group['owner_id'] == $_SESSION['user_id']);
            ?>
                <tr>
                    <td><?= htmlspecialchars($group['group_name']) ?></td>
                    <td>
                        <?php if ($isMember): ?>
                        <?php if (!$isOwner): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="group_id" value="<?= $group['group_id'] ?>">
                            <button type="submit" name="leave_group" class="btn btn-warning btn-sm">
                                <i class="fas fa-sign-out-alt"></i> مغادرة
                            </button>
                        </form>
                        <?php endif; ?>
                        <span class="text-success">عضو</span>
                        <?php elseif ($isPending): ?>
                        <span class="text-warning">بانتظار الموافقة</span>
                        <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="group_id" value="<?= $group['group_id'] ?>">
                            <button type="submit" name="join_group" class="btn btn-success btn-sm">
                                <i class="fas fa-sign-in-alt"></i> انضم
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($isOwner): ?>
                        <a href="manage_requests.php?group_id=<?= $group['group_id'] ?>"
                            class="btn btn-info btn-sm mt-1">
                            <i class="fas fa-tasks"></i> إدارة الطلبات
                        </a>
                        <a href="manage_members.php?group_id=<?= $group['group_id'] ?>"
                            class="btn btn-warning btn-sm mt-1">
                            <i class="fas fa-users-cog"></i> إدارة الأعضاء
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isMember): ?>
                        <a href="group_books.php?code=<?= $group['unique_code'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-book-open"></i> عرض الكتب
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-book-open"></i> عرض الكتب
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
.notifications-scroll-container {
    max-height: 350px;
    /* تحديد أقصى ارتفاع */
    overflow-y: auto;
    /* التمرير التلقائي */
    padding-right: 0.5rem;
    /* منع قص المحتوى */
}
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>