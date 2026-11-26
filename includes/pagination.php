<?php if (!isset($page) || !isset($totalPages) || !isset($paginationBase) || $totalPages <= 1) return; ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="<?php echo $paginationBase . ($page - 1); ?>">Prev</a>
    <?php else: ?>
        <span>Prev</span>
    <?php endif; ?>

    <input type="number" id="pageInput" min="1" max="<?php echo $totalPages; ?>" value="<?php echo $page; ?>"
        onkeydown="if(event.key==='Enter') goToPage(<?php echo $totalPages; ?>, '<?php echo $paginationBase; ?>')">

    <span class="pagination-info">of <?php echo $totalPages; ?></span>

    <button class="btn-sm" onclick="goToPage(<?php echo $totalPages; ?>, '<?php echo $paginationBase; ?>')">Go</button>

    <?php if ($page < $totalPages): ?>
        <a href="<?php echo $paginationBase . ($page + 1); ?>">Next</a>
    <?php else: ?>
        <span>Next</span>
    <?php endif; ?>
</div>
