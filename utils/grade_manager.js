// This file is part of Student Management System
//
// Student Management System is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Student Management System is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Student Management System.  If not, see <http://www.gnu.org/licenses/>.

// grade_manager.js
// JavaScript utility for student management system
// Features: form validation, dynamic grade calculation, table sorting, search/filter, dynamic grade entry, modals, local storage

(function () {
    // 1. Form Validation
    function validateForm(form) {
        let valid = true;
        let inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('input-error');
                valid = false;
            } else {
                input.classList.remove('input-error');
            }
        });
        return valid;
    }

    document.addEventListener('submit', function (e) {
        if (e.target.matches('.validate-form')) {
            if (!validateForm(e.target)) {
                e.preventDefault();
                showModal('Please fill in all required fields.');
            }
        }
    });

    // 2. Dynamic Grade Calculation
    function calculateAverage(grades) {
        if (!grades.length) return 0;
        let sum = grades.reduce((acc, val) => acc + parseFloat(val), 0);
        return (sum / grades.length).toFixed(2);
    }

    function updateGradeAverage(container) {
        let gradeInputs = container.querySelectorAll('.grade-input');
        let grades = Array.from(gradeInputs).map(input => input.value).filter(v => v !== '');
        let avg = calculateAverage(grades);
        let avgDisplay = container.querySelector('.grade-average');
        if (avgDisplay) avgDisplay.textContent = avg;
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('grade-input')) {
            let container = e.target.closest('.grade-container');
            if (container) updateGradeAverage(container);
        }
    });

    // 3. Interactive Table Sorting
    function sortTable(table, col, asc) {
        const dirModifier = asc ? 1 : -1;
        const tBody = table.tBodies[0];
        const rows = Array.from(tBody.querySelectorAll('tr'));
        const sortedRows = rows.sort((a, b) => {
            const aColText = a.querySelector(`td:nth-child(${col + 1})`).textContent.trim();
            const bColText = b.querySelector(`td:nth-child(${col + 1})`).textContent.trim();
            // Sort numerically for Average Grade (column index 3)
            if (col === 3) {
                const aNum = parseFloat(aColText) || 0;
                const bNum = parseFloat(bColText) || 0;
                return (aNum - bNum) * dirModifier;
            }
            return aColText.localeCompare(bColText, undefined, {numeric: true}) * dirModifier;
        });
        tBody.append(...sortedRows);
        table.querySelectorAll('th').forEach(th => th.classList.remove('sorted-asc', 'sorted-desc'));
        table.querySelector(`th:nth-child(${col + 1})`).classList.toggle(asc ? 'sorted-asc' : 'sorted-desc');
    }

    document.addEventListener('click', function (e) {
        if (e.target.matches('th.sortable')) {
            const th = e.target;
            const table = th.closest('table');
            const col = Array.from(th.parentNode.children).indexOf(th);
            const asc = !th.classList.contains('sorted-asc');
            sortTable(table, col, asc);
        }
    });

    // 4. Student Search/Filter
    document.addEventListener('input', function (e) {
        if (e.target.matches('.student-search')) {
            const value = e.target.value.toLowerCase();
            const table = document.querySelector(e.target.dataset.targetTable);
            if (!table) return;
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
            });
        }
    });

    // 5. Add/Remove Grade Entries Dynamically
    document.addEventListener('click', function (e) {
        if (e.target.matches('.add-grade-entry')) {
            const container = e.target.closest('.grade-container');
            const template = container.querySelector('.grade-entry-template');
            const clone = template.content.cloneNode(true);
            container.querySelector('.grade-entries').appendChild(clone);
            updateGradeAverage(container);
        }
        if (e.target.matches('.remove-grade-entry')) {
            const entry = e.target.closest('.grade-entry');
            if (entry) entry.remove();
            const container = e.target.closest('.grade-container');
            if (container) updateGradeAverage(container);
        }
    });

    // 6. Modal Dialogs
    function showModal(message) {
        let modal = document.getElementById('custom-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.innerHTML = '<div class="modal-bg"></div><div class="modal-content"><span class="modal-message"></span><button class="modal-close">OK</button></div>';
            document.body.appendChild(modal);
        }
        modal.querySelector('.modal-message').textContent = message;
        modal.style.display = 'block';
        modal.querySelector('.modal-close').onclick = function () {
            modal.style.display = 'none';
        };
        modal.querySelector('.modal-bg').onclick = function () {
            modal.style.display = 'none';
        };
    }
    window.showModal = showModal;

    // 7. Local Storage for User Preferences
    function savePreference(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    }
    function getPreference(key, defaultValue) {
        const val = localStorage.getItem(key);
        return val ? JSON.parse(val) : defaultValue;
    }
    window.savePreference = savePreference;
    window.getPreference = getPreference;

    // Example: Save table sort preference
    document.addEventListener('click', function (e) {
        if (e.target.matches('th.sortable')) {
            const th = e.target;
            const table = th.closest('table');
            const col = Array.from(th.parentNode.children).indexOf(th);
            const asc = !th.classList.contains('sorted-asc');
            savePreference(table.id + '_sort', {col, asc});
        }
    });
    // Example: Restore table sort preference on load
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('table').forEach(table => {
            const pref = getPreference(table.id + '_sort');
            if (pref && table.querySelectorAll('th.sortable')[pref.col]) {
                sortTable(table, pref.col, pref.asc);
            }
        });
    });
})();

