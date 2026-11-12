function openModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('open');
}

function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

function confirmDelete(url) {
    if (confirm('Are you sure you want to delete this record?')) {
        window.location.href = url;
    }
}

function goToPage(totalPages, baseUrl) {
    var input = document.getElementById('pageInput');
    var page  = parseInt(input.value);
    if (isNaN(page) || page < 1) { page = 1; }
    if (page > totalPages)       { page = totalPages; }
    window.location.href = baseUrl + page;
}

(function () {
    var params = new URLSearchParams(window.location.search);
    var msg = params.get('msg');

    var successMessages = {
        'added':        'Record added successfully.',
        'updated':      'Record updated successfully.',
        'deleted':      'Record deleted successfully.',
        'user_added':   'User added successfully.',
        'user_deleted': 'User deleted successfully.',
        'cat_added':    'Category added successfully.',
        'cat_deleted':  'Category deleted successfully.',
        'pwd_changed':  'Password changed successfully.'
    };

    if (msg && successMessages[msg]) {
        alert(successMessages[msg]);
        params.delete('msg');
        var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
})();
