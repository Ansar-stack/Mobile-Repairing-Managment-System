<?php
$currentPage = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar">
    <div class="sidebar-header">MR System</div>
    <nav>
        <ul>
            <li class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/dashboard/index.php">Dashboard</a>
            </li>
            <li class="<?php echo $currentPage === 'customers' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/customers/index.php">Customers</a>
            </li>
            <li class="<?php echo $currentPage === 'repairs' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/repairs/index.php">Mobile Repairs</a>
            </li>
            <li class="<?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/payments/index.php">Paid Amounts</a>
            </li>
            <li class="<?php echo $currentPage === 'items' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/items/index.php">Item Prices</a>
            </li>
            <li class="<?php echo $currentPage === 'expenses' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/expenses/index.php">Expenses</a>
            </li>
            <li class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                <a href="/mobile-repair/pages/settings/index.php">Settings</a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <span><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
        <a href="/mobile-repair/logout.php">Logout</a>
    </div>
</div>
