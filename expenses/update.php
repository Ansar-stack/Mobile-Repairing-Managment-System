<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$action = trim($_REQUEST['action'] ?? '');

if ($action === 'add') {
    $title  = addslashes(trim($_POST['title'] ?? ''));
    $catId  = intval($_POST['category_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);

    if ($title === '' || $catId <= 0 || $amount <= 0) { header('Location: index.php?msg=error'); exit; }

    $stmt = oci_parse($conn, "INSERT INTO EXPENSES (TITLE, CATEGORY_ID, AMOUNT, IS_DELETED, CREATED_AT, UPDATED_AT) VALUES ('$title', $catId, $amount, 0, SYSDATE, SYSDATE)");
    if (oci_execute($stmt)) {
        oci_free_statement($stmt); oci_close($conn);
        header('Location: index.php?msg=added'); exit;
    }
    oci_free_statement($stmt); oci_close($conn);
    header('Location: index.php?msg=error'); exit;
}

if ($action === 'edit') {
    $id     = intval($_POST['id'] ?? 0);
    $title  = addslashes(trim($_POST['title'] ?? ''));
    $catId  = intval($_POST['category_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);

    if ($id <= 0 || $title === '' || $catId <= 0 || $amount <= 0) { header('Location: index.php?msg=error'); exit; }

    $stmt = oci_parse($conn, "UPDATE EXPENSES SET TITLE='$title', CATEGORY_ID=$catId, AMOUNT=$amount, UPDATED_AT=SYSDATE WHERE EXPENSE_ID=$id AND IS_DELETED=0");
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

    $stmt = oci_parse($conn, "UPDATE EXPENSES SET IS_DELETED=1, UPDATED_AT=SYSDATE WHERE EXPENSE_ID=$id");
    if (oci_execute($stmt)) {
        oci_free_statement($stmt); oci_close($conn);
        header('Location: index.php?msg=deleted'); exit;
    }
    oci_free_statement($stmt); oci_close($conn);
    header('Location: index.php?msg=error'); exit;
}

header('Location: index.php'); exit;
