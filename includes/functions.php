
<?php
// دوال مساعدة
function getStatusColor($status) {
    switch ($status) {
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'warning';
    }
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'قيد المراجعة',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض'
    ];
    return $statuses[$status] ?? 'غير معروف';
}
?>