<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$action = trim($_REQUEST['action'] ?? '');

if ($action === 'add') {
    $name  = addslashes(trim($_POST['name'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($name === '' || $price < 0) { header('Location: index.php?msg=error'); exit; }

    $stmt = oci_parse($conn, "INSERT INTO ITEMS (NAME, PRICE, STOCK, IS_DELETED, CREATED_AT, UPDATED_AT) VALUES ('$name', $price, $stock, 0, SYSDATE, SYSDATE)");
    if (oci_execute($stmt)) {
        oci_free_statement($stmt); oci_close($conn);
        header('Location: index.php?msg=added'); exit;
    }
    oci_free_statement($stmt); oci_close($conn);
    header('Location: index.php?msg=error'); exit;
}

if ($action === 'edit') {
    $id    = intval($_POST['id'] ?? 0);
    $name  = addslashes(trim($_POST['name'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($id <= 0 || $name === '' || $price < 0) { header('Location: index.php?msg=error'); exit; }

    $stmt = oci_parse($conn, "UPDATE ITEMS SET NAME='$name', PRICE=$price, STOCK=$stock, UPDATED_AT=SYSDATE WHERE ITEM_ID=$id AND IS_DELETED=0");
    if (oci_execute($stmt)) {
        oci_free_statement($stmt); oci_close($conn);
        header('Location: index.php?msg=updated'); exit;
    }
    oci_free_statement($stmt); oci_close($conn);
    header('Location: index.php?msg=error'); exit;
}

if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) { header('Location: index.php?msg=error'); exit; }

    $stmt = oci_parse($conn, "UPDATE ITEMS SET IS_DELETED=1, UPDATED_AT=SYSDATE WHERE ITEM_ID=$id");
    if (oci_execute($stmt)) {
        oci_free_statement($stmt); oci_close($conn);
        header('Location: index.php?msg=deleted'); exit;
    }
    oci_free_statement($stmt); oci_close($conn);
    header('Location: index.php?msg=error'); exit;
}

header('Location: index.php'); exit;
