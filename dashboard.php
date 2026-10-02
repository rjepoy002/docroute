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
 * Return only the current routing event for documents relevant to this user.
 * Filtering the log table before checking for a newer event prevents an empty
 * dashboard from repeatedly scanning every historical document/log pair.
 */
function dashboard_rows(mysqli $db, string $where, int $userId, int $partyId = 0, string $partyColumn = ''): array
{
    $sql = "SELECT d.id_track, d.title, d.status AS document_status, l.status, l.date, l.sender, l.receiver,
        CONCAT_WS(', ', su.lname, NULLIF(CONCAT(su.fname, ' ', su.mname), '')) AS sender_name,
        CONCAT_WS(', ', ru.lname, NULLIF(CONCAT(ru.fname, ' ', ru.mname), '')) AS receiver_name
        FROM dr_logs l
        INNER JOIN dr_documents d ON d.id_track = l.id_track
        LEFT JOIN dr_users su ON su.id = l.sender
        LEFT JOIN dr_users ru ON ru.id = l.receiver
        WHERE $where
          AND NOT EXISTS (
              SELECT 1 FROM dr_logs newer
              WHERE newer.id_track = l.id_track AND newer.id > l.id
          )";

    $types = 'i';
    $params = [$userId];
    if ($partyId > 0 && in_array($partyColumn, ['l.sender', 'l.receiver'], true)) {
        $sql .= " AND $partyColumn = ?";
        $types .= 'i';
        $params[] = $partyId;
    }
    $sql .= ' ORDER BY l.id DESC';

    $statement = $db->prepare($sql);
    if (count($params) === 1) {
        $statement->bind_param($types, $params[0]);
    } else {
        $statement->bind_param($types, $params[0], $params[1]);
    }
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

function dashboard_groups(mysqli $db, string $where, int $userId, string $partyColumn): array
{
    $labelJoin = $partyColumn === 'sender' ? 'su' : 'ru';
    $sql = "SELECT l.$partyColumn AS party_id,
        CONCAT_WS(', ', $labelJoin.lname, NULLIF(CONCAT($labelJoin.fname, ' ', $labelJoin.mname), '')) AS party_name,
        COUNT(*) AS document_count, MAX(l.id) AS latest_log_id
        FROM dr_logs l
        INNER JOIN dr_documents d ON d.id_track = l.id_track
        LEFT JOIN dr_users su ON su.id = l.sender
        LEFT JOIN dr_users ru ON ru.id = l.receiver
        WHERE $where
          AND NOT EXISTS (
              SELECT 1 FROM dr_logs newer
              WHERE newer.id_track = l.id_track AND newer.id > l.id
          )
        GROUP BY l.$partyColumn, $labelJoin.lname, $labelJoin.fname, $labelJoin.mname
        ORDER BY latest_log_id DESC";

    $statement = $db->prepare($sql);
    $statement->bind_param('i', $userId);
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

$incomingWhere = "l.receiver = ? AND l.status = 'Pending'";
$receivableWhere = "l.receiver = ? AND l.status <> 'Pending' AND l.status <> 'Closed' AND d.status = 'open'";
$outgoingWhere = "l.sender = ? AND l.status = 'Pending'";
$releasedWhere = "d.author = ? AND l.status <> 'Pending' AND l.status <> 'Closed'";
$closedWhere = "d.author = ? AND d.status = 'closed'";

$incoming = dashboard_rows($conn, $incomingWhere, $userId, $selectedGroup === 'incoming' ? $selectedParty : 0, 'l.sender');
$receivable = dashboard_rows($conn, $receivableWhere, $userId, $selectedGroup === 'receivables' ? $selectedParty : 0, 'l.sender');
$outgoing = dashboard_rows($conn, $outgoingWhere, $userId, $selectedGroup === 'outgoing' ? $selectedParty : 0, 'l.receiver');
$released = dashboard_rows($conn, $releasedWhere, $userId);
$closed = dashboard_rows($conn, $closedWhere, $userId);

require __DIR__ . '/includes/app-shell.php';
?>
<section class="stats" aria-label="Document summary">
    <article class="stat card"><span>Incoming</span><strong><?= count($incoming) ?></strong></article>
    <article class="stat card"><span>Receivables</span><strong><?= count($receivable) ?></strong></article>
    <article class="stat card"><span>Outgoing</span><strong><?= count($outgoing) ?></strong></article>
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

function group_section(string $title, array $rows, string $group, string $empty): void
{
    ?>
    <section>
        <div class="section-heading"><h2><?= e($title) ?> by contact</h2></div>
        <div class="panel data-wrap">
            <?php if (!$rows): ?>
                <div class="empty"><strong><?= e($empty) ?></strong></div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Contact</th><th>Documents</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e($row['party_name'] ?: 'Unknown') ?></td>
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
    group_section('Incoming documents', dashboard_groups($conn, $incomingWhere, $userId, 'sender'), 'incoming', 'No incoming documents.');
    group_section('Receivables', dashboard_groups($conn, $receivableWhere, $userId, 'sender'), 'receivables', 'No receivable documents.');
    group_section('Outgoing documents', dashboard_groups($conn, $outgoingWhere, $userId, 'receiver'), 'outgoing', 'No outgoing documents.');
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
