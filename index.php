<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
session_start();

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid request.');
    }

    $old = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'phone'     => trim($_POST['phone'] ?? ''),
        'dob'       => $_POST['dob'] ?? '',
        'gender'    => $_POST['gender'] ?? '',
        'address'   => trim($_POST['address'] ?? ''),
    ];

    if ($old['full_name'] === '' || mb_strlen($old['full_name']) > 100) $errors[] = 'Enter a valid name.';

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';

    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $old['phone'])) $errors[] = 'Enter a valid phone number.';

    $d = DateTimeImmutable::createFromFormat('Y-m-d', $old['dob']);

    if (!$d || $d->format('Y-m-d') !== $old['dob'] || $d > new DateTimeImmutable('today')) $errors[] = 'Enter a valid date of birth.';

    if (!in_array($old['gender'], ['Male', 'Female', 'Other'], true)) $errors[] = 'Select a gender.';

    if ($old['address'] === '') $errors[] = 'Enter an address.';

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, phone, dob, gender, address)
                 VALUES (:full_name, :email, :phone, :dob, :gender, :address)'
            );
            $stmt->execute($old);
            $_SESSION['flash'] = 'User saved successfully.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $ex) {
            $errors[] = $ex->getCode() === '23000' ? 'This email already exists.' : 'Database error. Try again.';
        }
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$users = $pdo->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <section class="card">
        <h1>Add User Details</h1>

        <?php if ($flash): ?><p class="alert success"><?= e($flash) ?></p><?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

            <div class="grid-2">
                <label>Full Name
                    <input type="text" name="full_name" maxlength="100" required value="<?= e($old['full_name'] ?? '') ?>">
                </label>
                <label>Email
                    <input type="email" name="email" required value="<?= e($old['email'] ?? '') ?>">
                </label>
                <label>Phone
                    <input type="tel" name="phone" required value="<?= e($old['phone'] ?? '') ?>">
                </label>
                <label>Date of Birth
                    <input type="date" name="dob" max="<?= date('Y-m-d') ?>" required value="<?= e($old['dob'] ?? '') ?>">
                </label>
            </div>

            <fieldset>
                <legend>Gender</legend>
                <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                    <label class="inline">
                        <input type="radio" name="gender" value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'checked' : '' ?>> <?= $g ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <label>Address
                <textarea name="address" rows="3" required><?= e($old['address'] ?? '') ?></textarea>
            </label>

            <button type="submit">Save User</button>
        </form>
    </section>

    <section class="card">
        <h2>Saved Users (<?= count($users) ?>)</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Gender</th><th>Address</th><th>Added On</th></tr>
                </thead>
                <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="8" class="empty">No users yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int) $u['id'] ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['phone']) ?></td>
                        <td><?= e(date('d M Y', strtotime($u['dob']))) ?></td>
                        <td><?= e($u['gender']) ?></td>
                        <td><?= e($u['address']) ?></td>
                        <td><?= e(date('d M Y, h:i A', strtotime($u['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>