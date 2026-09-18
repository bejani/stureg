<?php
require __DIR__ . '/../includes/functions.php';
require_login('admin');
audit('export','students',null,$_GET['format'] ?? 'json');

$format = strtolower($_GET['format'] ?? 'json');
if (!in_array($format, ['json', 'csv'], true)) {
    http_response_code(400);
    exit('فرمت خروجی نامعتبر است.');
}

$sql = "SELECT
    u.id AS user_id, u.name, u.mobile, u.created_at AS account_created_at,
    p.grade, p.study_field, p.national_id, p.birth_date, p.gender,
    p.parent_name, p.parent_phone, p.parent_relation,
    p.household_status, p.primary_caregiver, p.caregiver_phone,
    p.address, p.emergency_contact, p.emergency_phone,
    p.health_notes, p.allergies, p.counseling_notes,
    p.strengths, p.difficult_subjects, p.academic_goal, p.learning_style,
    p.interests, p.current_mood, p.communication_preference,
    p.study_resources, p.support_request, p.student_note,
    p.consent, p.consent_at, p.emergency_flag, p.case_status, p.followup_note,
    p.next_followup_date, p.last_followup_at, p.updated_at AS profile_updated_at
    FROM users u
    LEFT JOIN student_profiles p ON p.user_id = u.id
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC";
$rows = db()->query($sql)->fetchAll();
$messages = db()->query("SELECT student_id, sender_role, body, is_read, created_at FROM messages ORDER BY student_id, created_at")->fetchAll();
$messages_by_student = [];
foreach ($messages as $message) {
    $messages_by_student[$message['student_id']][] = $message;
}
foreach ($rows as &$row) {
    $row['messages'] = $messages_by_student[$row['user_id']] ?? [];
}
unset($row);

$filename = 'stureg-students-' . date('Y-m-d-His');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="' . $filename . '.' . $format . '"');

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'app' => APP_NAME,
        'exported_at' => date(DATE_ATOM),
        'count' => count($rows),
        'students' => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
// UTF-8 BOM helps Microsoft Excel recognize Persian text correctly.
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
$headers = array_keys($rows[0] ?? [
    'user_id' => null, 'name' => null, 'mobile' => null, 'account_created_at' => null,
]);
fputcsv($out, $headers);
foreach ($rows as $row) {
    $values = [];
    foreach ($headers as $header) {
        $value = $header === 'messages'
            ? json_encode($row[$header] ?? [], JSON_UNESCAPED_UNICODE)
            : (string)($row[$header] ?? '');
        // Prevent spreadsheet formula injection when opening exported data.
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            $value = "'" . $value;
        }
        $values[] = $value;
    }
    fputcsv($out, $values);
}
fclose($out);
exit;
