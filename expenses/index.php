<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$pageTitle = 'Expenses';
$msg = ''; $msgType = '';
$perPage = 10;
$page    = max(1, intval($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$search  = trim($_GET['search'] ?? '');
$filterCat = trim($_GET['filter_category'] ?? '');

$stmt = oci_parse($conn, 'SELECT CATEGORY_ID, NAME FROM EXPENSE_CATEGORIES WHERE IS_DELETED = 0 ORDER BY NAME');
oci_execute($stmt);
$categories = [];
while ($row = oci_fetch_assoc($stmt)) { $categories[] = $row; }
oci_free_statement($stmt);

$where = 'WHERE E.IS_DELETED = 0';
if ($filterCat !== '') { $where .= ' AND E.CATEGORY_ID = ' . intval($filterCat); }
if ($search !== '') {
    $s = addslashes(strtoupper($search));
    $where .= " AND (UPPER(E.TITLE) LIKE '%$s%' OR CAST(E.EXPENSE_ID AS VARCHAR2(20)) LIKE '%$s%')";
}

$stmt = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM EXPENSES E $where");
oci_execute($stmt);
$totalRows  = oci_fetch_assoc($stmt)['CNT'];
$totalPages = max(1, ceil($totalRows / $perPage));
oci_free_statement($stmt);

$maxRow = $offset + $perPage;
$stmt = oci_parse($conn, "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT E.EXPENSE_ID, E.TITLE, E.CATEGORY_ID, E.AMOUNT, E.CREATED_AT, C.NAME AS CATEGORY_NAME FROM EXPENSES E LEFT JOIN EXPENSE_CATEGORIES C ON E.CATEGORY_ID = C.CATEGORY_ID $where ORDER BY E.EXPENSE_ID DESC) a WHERE ROWNUM <= $maxRow) WHERE rnum > $offset");
oci_execute($stmt);
$expenses = [];
while ($row = oci_fetch_assoc($stmt)) { $expenses[] = $row; }
oci_free_statement($stmt);
oci_close($conn);

if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
    $msg = 'An error occurred.'; $msgType = 'error';
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<div class="content">
    <div class="page-header">
        <h1>Expenses</h1>
        <button class="btn" onclick="openModal('addModal')">Add Expense</button>
    </div>
    <?php if ($msg !== ''): ?>
        <div class="msg <?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <form method="GET">
        <div class="filters">
            <div class="form-group">
                <label>Category</label>
                <select name="filter_category">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['CATEGORY_ID']; ?>" <?php echo $filterCat == $cat['CATEGORY_ID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['NAME']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Title or ID..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group"><label>&nbsp;</label><button type="submit" class="btn">Apply</button></div>
            <div class="form-group"><label>&nbsp;</label><a href="index.php" class="btn">Clear</a></div>
        </div>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Title</th><th>Category</th><th>Amount</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="6">No records found.</td></tr>
                <?php else: foreach ($expenses as $e): ?>
                <tr>
                    <td><?php echo $e['EXPENSE_ID']; ?></td>
                    <td><?php echo htmlspecialchars($e['TITLE']); ?></td>
                    <td><?php echo htmlspecialchars($e['CATEGORY_NAME'] ?? '-'); ?></td>
                    <td><?php echo number_format($e['AMOUNT'], 2); ?></td>
                    <td><?php echo $e['CREATED_AT'] ? date('Y-m-d', strtotime($e['CREATED_AT'])) : ''; ?></td>
                    <td>
                        <button class="btn-sm" onclick="openEditModal('<?php echo $e['EXPENSE_ID']; ?>','<?php echo addslashes($e['TITLE']); ?>','<?php echo $e['CATEGORY_ID']; ?>','<?php echo $e['AMOUNT']; ?>')">Edit</button>
                        <button class="btn-sm btn-danger" onclick="confirmDelete('update.php?action=delete&id=<?php echo $e['EXPENSE_ID']; ?>')">Delete</button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php $paginationBase = '?filter_category=' . urlencode($filterCat) . '&search=' . urlencode($search) . '&page='; require '../../includes/pagination.php'; ?>
</div>

<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <h3>Add Expense</h3>
        <form method="POST" action="update.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Title *</label><input type="text" name="title" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['CATEGORY_ID']; ?>"><?php echo htmlspecialchars($cat['NAME']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Amount *</label><input type="number" step="0.01" name="amount" required></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Save</button>
                <button type="button" class="btn" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3>Edit Expense</h3>
        <form method="POST" action="update.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Title *</label><input type="text" name="title" id="edit_title" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="edit_category_id" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['CATEGORY_ID']; ?>"><?php echo htmlspecialchars($cat['NAME']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Amount *</label><input type="number" step="0.01" name="amount" id="edit_amount" required></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Update</button>
                <button type="button" class="btn" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="/mobile-repair/assets/js/main.js"></script>
<script>
function openEditModal(id, title, catId, amount) {
    document.getElementById('edit_id').value          = id;
    document.getElementById('edit_title').value       = title;
    document.getElementById('edit_category_id').value = catId;
    document.getElementById('edit_amount').value      = amount;
    openModal('editModal');
}
</script>
<?php require_once '../../includes/footer.php'; ?>
