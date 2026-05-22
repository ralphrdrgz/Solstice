if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.html');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');
$inquiry   = trim($_POST['inquiry']   ?? '');
$message   = trim($_POST['message']   ?? '');

if (empty($full_name) || empty($email) || empty($inquiry) || empty($message)) {
    header('Location: /index.html?status=error&reason=missing_fields');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /index.html?status=error&reason=invalid_email');
    exit;
}

$entries_dir = '/tmp/entries';
$csv_file    = $entries_dir . '/entries.csv';

if (!is_dir($entries_dir)) {
    mkdir($entries_dir, 0755, true);
}

$write_headers = !file_exists($csv_file);

$handle = fopen($csv_file, 'a');

if ($handle === false) {
    header('Location: /index.html?status=error&reason=file_error');
    exit;
}

if ($write_headers) {
    fputcsv($handle, ['Timestamp', 'Full Name', 'Email', 'Inquiry', 'Message']);
}

fputcsv($handle, [date('Y-m-d H:i:s'), $full_name, $email, $inquiry, $message]);

fclose($handle);

header('Location: /index.html?status=success');
