<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI execution only.' . PHP_EOL);
}

require_once __DIR__ . '/../config/database.php';

const MODE_MIGRATE = 'migrate';
const MODE_DRY_RUN = 'dry-run';
const MODE_STATUS = 'status';

function usage(): void
{
    echo "Usage:\n";
    echo "  php scripts/migrate_legacy_passwords.php --dry-run\n";
    echo "  php scripts/migrate_legacy_passwords.php --status\n";
    echo "  php scripts/migrate_legacy_passwords.php\n";
}

function account_label(array $account): string
{
    $username = trim((string) ($account['username'] ?? ''));
    return 'ID ' . (int) $account['id'] . ($username === '' ? '' : ' - ' . $username);
}

function password_column_is_large_enough(mysqli $db): bool
{
    $result = $db->query("SHOW COLUMNS FROM dr_users LIKE 'password'");
    $column = $result ? $result->fetch_assoc() : null;
    if ($column === null) {
        fwrite(STDERR, "Unable to verify dr_users.password. Migration stopped.\n");
        exit(1);
    }

    if (!preg_match('/^varchar\\((\\d+)\\)$/i', (string) $column['Type'], $matches) || (int) $matches[1] < 255) {
        fwrite(STDERR, "Migration stopped: dr_users.password must be VARCHAR(255) or larger before migrating passwords.\n");
        exit(1);
    }

    return true;
}

function inspect_accounts(mysqli $db): array
{
    $result = $db->query('SELECT id, username, password FROM dr_users ORDER BY id');
    if ($result === false) {
        throw new RuntimeException('Unable to inspect user accounts.');
    }

    $summary = [
        'total' => 0,
        'hashed' => 0,
        'legacy' => [],
        'review' => [],
    ];

    while ($account = $result->fetch_assoc()) {
        $summary['total']++;
        $storedPassword = $account['password'];

        if ($storedPassword === null) {
            $summary['review'][] = ['account' => $account, 'reason' => 'NULL password'];
            continue;
        }
        if ($storedPassword === '') {
            $summary['review'][] = ['account' => $account, 'reason' => 'empty password'];
            continue;
        }

        $passwordInfo = password_get_info((string) $storedPassword);
        if (!empty($passwordInfo['algo'])) {
            $summary['hashed']++;
            continue;
        }

        $summary['legacy'][] = $account;
    }

    return $summary;
}

function print_summary(array $summary): void
{
    echo 'Total users: ' . $summary['total'] . PHP_EOL;
    echo 'Already hashed: ' . $summary['hashed'] . PHP_EOL;
    echo 'Legacy plaintext: ' . count($summary['legacy']) . PHP_EOL;
    echo 'Empty/invalid for review: ' . count($summary['review']) . PHP_EOL;
}

$argument = $argv[1] ?? '';
$mode = MODE_MIGRATE;
if ($argument === '--dry-run') {
    $mode = MODE_DRY_RUN;
} elseif ($argument === '--status') {
    $mode = MODE_STATUS;
} elseif ($argument !== '') {
    usage();
    exit(1);
}

echo "DocRoute Legacy Password Migration\n\n";
$databaseResult = $conn->query('SELECT DATABASE() AS database_name');
$databaseName = $databaseResult ? (string) ($databaseResult->fetch_assoc()['database_name'] ?? '') : '';
echo 'Database: ' . ($databaseName !== '' ? $databaseName : '[unavailable]') . "\n\n";

password_column_is_large_enough($conn);

try {
    $summary = inspect_accounts($conn);
} catch (Throwable $error) {
    error_log('Password migration inspection failed: ' . $error->getMessage());
    fwrite(STDERR, "Unable to inspect accounts. Migration stopped.\n");
    exit(1);
}

print_summary($summary);

if ($mode === MODE_STATUS) {
    exit(0);
}

if ($summary['review']) {
    echo "\nAccounts requiring manual review:\n";
    foreach ($summary['review'] as $entry) {
        echo '[REVIEW] ' . account_label($entry['account']) . ' - ' . $entry['reason'] . PHP_EOL;
    }
}

if ($mode === MODE_DRY_RUN) {
    echo "\nDry run: no database changes will be made.\n";
    foreach ($summary['legacy'] as $account) {
        echo '[WOULD HASH] ' . account_label($account) . PHP_EOL;
    }
    exit(0);
}

if (!$summary['legacy']) {
    echo "\nNo legacy plaintext passwords require migration.\n";
    exit(0);
}

echo "\nIMPORTANT:\n";
echo 'Create a backup of ' . ($databaseName !== '' ? $databaseName : 'the database') . " before continuing.\n";
echo "Password hashing is one-way and cannot be reversed.\n\n";
echo 'This operation will replace ' . count($summary['legacy']) . " plaintext passwords with secure PHP password hashes.\n";
echo "Type MIGRATE to continue: ";
$confirmation = trim((string) fgets(STDIN));
if ($confirmation !== 'MIGRATE') {
    echo "Migration cancelled.\n";
    exit(0);
}

try {
    $conn->begin_transaction();
    $update = $conn->prepare('UPDATE dr_users SET password = ? WHERE id = ?');
    if ($update === false) {
        throw new RuntimeException('Unable to prepare password update.');
    }

    $migrated = 0;
    foreach ($summary['legacy'] as $account) {
        $newHash = password_hash((string) $account['password'], PASSWORD_DEFAULT);
        if ($newHash === false) {
            throw new RuntimeException('Password hash generation failed.');
        }

        $userId = (int) $account['id'];
        $update->bind_param('si', $newHash, $userId);
        if (!$update->execute() || $update->affected_rows !== 1) {
            throw new RuntimeException('Password update failed.');
        }
        $migrated++;
        echo '[HASHED] ' . account_label($account) . PHP_EOL;
    }

    $conn->commit();
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Password migration rolled back: ' . $error->getMessage());
    fwrite(STDERR, "Migration failed and was rolled back. No credentials were displayed.\n");
    exit(1);
}

$after = inspect_accounts($conn);
echo "\nMigration completed successfully.\n\n";
echo 'Migrated: ' . $migrated . PHP_EOL;
print_summary($after);
echo "\nNo plaintext passwords were displayed. Test several existing accounts before considering the migration complete.\n";
