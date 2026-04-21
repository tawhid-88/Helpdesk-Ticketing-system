// Helpdesk Ticketing System - app.js

document.addEventListener('DOMContentLoaded', function() {

    // Auto-dismiss alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    for (var i = 0; i < alerts.length; i++) {
        (function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.4s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 400);
            }, 5000);
        })(alerts[i]);
    }

    // Confirm before dangerous actions
    var dangerBtns = document.querySelectorAll('[data-confirm]');
    for (var j = 0; j < dangerBtns.length; j++) {
        dangerBtns[j].addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    }

    // Search form - prevent empty submit
    var searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var input = this.querySelector('input[name="search"]');
            if (input && input.value.trim() === '') {
                input.value = '';
            }
        });
    }

    // File input label update
    var fileInputs = document.querySelectorAll('input[type="file"]');
    for (var k = 0; k < fileInputs.length; k++) {
        fileInputs[k].addEventListener('change', function() {
            var label = this.parentElement.querySelector('.file-label');
            if (label && this.files.length > 0) {
                label.textContent = this.files[0].name;
            }
        });
    }
});
