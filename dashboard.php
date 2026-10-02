<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_auth();
require_once __DIR__ . '/config/database.php';

$page_title = 'Dashboard';
$page_heading = 'Document dashboard';
$active_page = 'dashboard';
$userId = (int) $_SESSION['user_id'];
$user = current_user($conn);

if ($user === null) {
    $_SESSION = [];
    flash('error', 'Your session is no longer valid. Please sign in again.');
    redirect('index.php');
}

$grouped = !empty($user['isgroup']);
$selectedGroup = (string) ($_GET['group'] ?? '');
$selectedParty = (int) ($_GET['party'] ?? 0);

/**
 * Load each relevant document's latest routing row once. The legacy data has
 * many history rows per tracking number; deriving MAX(id) once avoids the
 * grouped dashboard's previous three correlated aggregate queries.
 */
function dashboard_current_routes(mysqli $db, int $userId): array
{
    $sql = "SELECT d.id_track, d.title, d.author, d.status AS document_status,
        l.id AS log_id, l.status, l.date, l.sender, l.receiver,
        CONCAT_WS(', ', su.lname, NULLIF(CONCAT(su.fname, ' ', su.mname), '')) AS sender_name,
        CONCAT_WS(', ', ru.lname, NULLIF(CONCAT(ru.fname, ' ', ru.mname), '')) AS receiver_name
        FROM dr_logs l
        INNER JOIN (
            SELECT id_track, MAX(id) AS latest_id
            FROM dr_logs
            GROUP BY id_track
        ) latest ON latest.latest_id = l.id
        INNER JOIN dr_documents d ON d.id_track = l.id_track
        LEFT JOIN dr_users su ON su.id = l.sender
        LEFT JOIN dr_users ru ON ru.id = l.receiver
        WHERE l.receiver = ? OR l.sender = ? OR d.author = ?
        ORDER BY l.id DESC";

    $statement = $db->prepare($sql);
    $statement->bind_param('iii', $userId, $userId, $userId);
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

function dashboard_sections(array $routes, int $userId): array
{
    $sections = [
        'incoming' => [],
        'receivables' => [],
        'outgoing' => [],
        'released' => [],
        'closed' => [],
    ];

    foreach ($routes as $route) {
        $isReceiver = (int) $route['receiver'] === $userId;
        $isSender = (int) $route['sender'] === $userId;
        $isAuthor = (int) $route['author'] === $userId;
        $status = (string) $route['status'];

        if ($isReceiver && $status === 'Pending') {
            $sections['incoming'][] = $route;
        }
        if ($isReceiver && $status !== 'Pending' && $status !== 'Closed' && strtolower((string) $route['document_status']) === 'open') {
            $sections['receivables'][] = $route;
        }
        if ($isSender && $status === 'Pending') {
            $sections['outgoing'][] = $route;
        }
        if ($isAuthor && $status !== 'Pending' && $status !== 'Closed') {
            $sections['released'][] = $route;
        }
        if ($isAuthor && strtolower((string) $route['document_status']) === 'closed') {
            $sections['closed'][] = $route;
        }
    }

    return $sections;
}

function filter_group_rows(array $rows, int $partyId, string $partyKey): array
{
    if ($partyId < 1) {
        return $rows;
    }

    return array_values(array_filter($rows, function (array $row) use ($partyId, $partyKey): bool {
        return (int) $row[$partyKey] === $partyId;
    }));
}

function group_rows(array $rows, string $partyKey, string $nameKey): array
{
    $groups = [];
    foreach ($rows as $row) {
        $partyId = (int) $row[$partyKey];
        if (!isset($groups[$partyId])) {
            $groups[$partyId] = [
                'party_id' => $partyId,
                'party_name' => $row[$nameKey] ?: 'Unknown',
                'document_count' => 0,
            ];
        }
        $groups[$partyId]['document_count']++;
    }

    return array_values($groups);
}

$routes = dashboard_current_routes($conn, $userId);
$sections = dashboard_sections($routes, $userId);

$incoming = filter_group_rows($sections['incoming'], $selectedGroup === 'incoming' ? $selectedParty : 0, 'sender');
$receivable = filter_group_rows($sections['receivables'], $selectedGroup === 'receivables' ? $selectedParty : 0, 'sender');
$outgoing = filter_group_rows($sections['outgoing'], $selectedGroup === 'outgoing' ? $selectedParty : 0, 'receiver');
$released = $sections['released'];
$closed = $sections['closed'];

require __DIR__ . '/includes/app-shell.php';
?>
<section class="stats" aria-label="Document summary">
    <article class="stat card"><span>Incoming</span><strong><?= count($sections['incoming']) ?></strong></article>
    <article class="stat card"><span>Receivables</span><strong><?= count($sections['receivables']) ?></strong></article>
    <article class="stat card"><span>Outgoing</span><strong><?= count($sections['outgoing']) ?></strong></article>
    <article class="stat card"><span>Released</span><strong><?= count($released) ?></strong></article>
    <article class="stat card"><span>Closed</span><strong><?= count($closed) ?></strong></article>
</section>

<?php
function document_section(string $title, array $rows, string $empty): void
{
    ?>
    <section>
        <div class="section-heading"><h2><?= e($title) ?></h2></div>
        <div class="panel data-wrap">
            <?php if (!$rows): ?>
                <div class="empty"><strong><?= e($empty) ?></strong><br>Documents will appear here as their routing status changes.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Tracking no.</th><th>Title</th><th>Sender / receiver</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e($row['date']) ?></td>
                            <td><?= e($row['id_track']) ?></td>
                            <td><?= e($row['title']) ?></td>
                            <td><?= e(($row['sender_name'] ?: 'Unknown') . ' → ' . ($row['receiver_name'] ?: 'Unknown')) ?></td>
                            <td><span class="badge <?= status_class($row['status']) ?>"><?= e($row['status']) ?></span></td>
                            <td><a class="btn btn-secondary" href="<?= url('documents/view.php?tracking=' . rawurlencode($row['id_track'])) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function group_section(string $title, array $rows, string $group, string $empty, string $partyKey, string $nameKey): void
{
    $groups = group_rows($rows, $partyKey, $nameKey);
    ?>
    <section>
        <div class="section-heading"><h2><?= e($title) ?> by contact</h2></div>
        <div class="panel data-wrap">
            <?php if (!$groups): ?>
                <div class="empty"><strong><?= e($empty) ?></strong></div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Contact</th><th>Documents</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($groups as $row): ?>
                        <tr>
                            <td><?= e($row['party_name']) ?></td>
                            <td><?= (int) $row['document_count'] ?></td>
                            <td><a class="btn btn-secondary" href="<?= url('dashboard.php?group=' . rawurlencode($group) . '&party=' . (int) $row['party_id']) ?>">View documents</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

if ($grouped && $selectedGroup === '') {
    group_section('Incoming documents', $sections['incoming'], 'incoming', 'No incoming documents.', 'sender', 'sender_name');
    group_section('Receivables', $sections['receivables'], 'receivables', 'No receivable documents.', 'sender', 'sender_name');
    group_section('Outgoing documents', $sections['outgoing'], 'outgoing', 'No outgoing documents.', 'receiver', 'receiver_name');
} else {
    if ($selectedGroup !== ''): ?>
        <p><a href="<?= url('dashboard.php') ?>">Back to dashboard</a></p>
    <?php endif;
    document_section('Incoming documents', $incoming, 'No incoming documents.');
    document_section('Receivables', $receivable, 'No receivable documents.');
    document_section('Outgoing documents', $outgoing, 'No outgoing documents found.');
}

document_section('Released / forwarded documents', $released, 'No released documents.');
document_section('Closed documents', $closed, 'No closed documents.');
require __DIR__ . '/includes/footer.php';
?>
