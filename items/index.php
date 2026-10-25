<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$pageTitle = 'Item Prices';
$msg = ''; $msgType = '';
$perPage   = 10;
$page      = max(1, intval($_GET['page'] ?? 1));
$offset    = ($page - 1) * $perPage;
$search    = trim($_GET['search'] ?? '');
$foundItem = null;

if ($search !== '') {
    $s = addslashes(strtoupper($search));
    $stmt = oci_parse($conn, "SELECT ITEM_ID, NAME, PRICE FROM ITEMS WHERE IS_DELETED = 0 AND UPPER(NAME) LIKE '%$s%' AND ROWNUM = 1");
    oci_execute($stmt);
    $foundItem = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
}

$stmt = oci_parse($conn, 'SELECT COUNT(*) AS CNT FROM ITEMS WHERE IS_DELETED = 0');
oci_execute($stmt);
$totalRows  = oci_fetch_assoc($stmt)['CNT'];
$totalPages = max(1, ceil($totalRows / $perPage));
oci_free_statement($stmt);

$maxRow = $offset + $perPage;
$stmt = oci_parse($conn, "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT ITEM_ID, NAME, PRICE, STOCK, CREATED_AT, UPDATED_AT FROM ITEMS WHERE IS_DELETED = 0 ORDER BY ITEM_ID DESC) a WHERE ROWNUM <= $maxRow) WHERE rnum > $offset");
oci_execute($stmt);
$items = [];
while ($row = oci_fetch_assoc($stmt)) { $items[] = $row; }
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
        <h1>Item Prices</h1>
        <button class="btn" onclick="openModal('addModal')">Add Item</button>
    </div>
    <?php if ($msg !== ''): ?>
        <div class="msg <?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <form method="GET">
        <div class="filters">
            <div class="form-group">
                <label>Search by Name</label>
                <input type="text" name="search" placeholder="Item name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group"><label>&nbsp;</label><button type="submit" class="btn">Search</button></div>
            <?php if ($search !== ''): ?>
            <div class="form-group"><label>&nbsp;</label><a href="index.php" class="btn">Clear</a></div>
            <?php endif; ?>
        </div>
    </form>
    <?php if ($search !== ''): ?>
        <?php if ($foundItem): ?>
            <div class="item-result">
                <div class="item-name"><?php echo htmlspecialchars($foundItem['NAME']); ?></div>
                <div class="item-price"><?php echo number_format($foundItem['PRICE'], 2); ?></div>
            </div>
        <?php else: ?>
            <div class="msg error">No item found with that name.</div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6">No records found.</td></tr>
                <?php else: foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['ITEM_ID']; ?></td>
                    <td><?php echo htmlspecialchars($item['NAME']); ?></td>
                    <td><?php echo number_format($item['PRICE'], 2); ?></td>
                    <td><?php echo $item['STOCK']; ?></td>
                    <td><?php echo $item['CREATED_AT'] ? date('Y-m-d', strtotime($item['CREATED_AT'])) : ''; ?></td>
                    <td>
                        <button class="btn-sm" onclick="openEditModal('<?php echo $item['ITEM_ID']; ?>','<?php echo addslashes($item['NAME']); ?>','<?php echo $item['PRICE']; ?>','<?php echo $item['STOCK']; ?>')">Edit</button>
                        <button class="btn-sm btn-danger" onclick="confirmDelete('update.php?action=delete&id=<?php echo $item['ITEM_ID']; ?>')">Delete</button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php $paginationBase = '?search=' . urlencode($search) . '&page='; require '../../includes/pagination.php'; ?>
</div>

<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <h3>Add Item</h3>
        <form method="POST" action="update.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Price *</label><input type="number" step="0.01" name="price" required></div>
            <div class="form-group"><label>Stock</label><input type="number" name="stock" value="0"></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Save</button>
                <button type="button" class="btn" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3>Edit Item</h3>
        <form method="POST" action="update.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Name *</label><input type="text" name="name" id="edit_name" required></div>
            <div class="form-group"><label>Price *</label><input type="number" step="0.01" name="price" id="edit_price" required></div>
            <div class="form-group"><label>Stock</label><input type="number" name="stock" id="edit_stock" value="0"></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Update</button>
                <button type="button" class="btn" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="/mobile-repair/assets/js/main.js"></script>
<script>
function openEditModal(id, name, price, stock) {
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock').value = stock;
    openModal('editModal');
}
</script>
<?php require_once '../../includes/footer.php'; ?>
