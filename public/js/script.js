/**
 * MediCare Hospital Management System
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNotifications();
    initializeDropdowns();
    initializeModals();
    initializeTooltips();
    initializeDatePickers();
    initializeFormValidation();
});

/**
 * Notification System
 */
function initializeNotifications() {
    const notificationBell = document.querySelector('.notification-bell');
    
    if (notificationBell) {
        notificationBell.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle notification dropdown
            const dropdown = document.createElement('div');
            dropdown.className = 'notification-dropdown';
            dropdown.innerHTML = `
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <a href="#" class="mark-all-read">Mark all as read</a>
                </div>
                <ul class="notification-list">
                    <li class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="notification-content">
                            <p>Your appointment with Dr. John Smith has been confirmed.</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                        <div class="notification-actions">
                            <button class="mark-read"><i class="fas fa-check"></i></button>
                        </div>
                    </li>
                    <li class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="notification-content">
                            <p>Your test results are now available. Click to view.</p>
                            <span class="notification-time">5 hours ago</span>
                        </div>
                        <div class="notification-actions">
                            <button class="mark-read"><i class="fas fa-check"></i></button>
                        </div>
                    </li>
                    <li class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="notification-content">
                            <p>You have a new message from Dr. Sarah Johnson.</p>
                            <span class="notification-time">Yesterday</span>
                        </div>
                    </li>
                </ul>
                <div class="notification-footer">
                    <a href="notifications.html">View all notifications</a>
                </div>
            `;
            
            // Position the dropdown
            const rect = notificationBell.getBoundingClientRect();
            dropdown.style.position = 'absolute';
            dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
            dropdown.style.right = (window.innerWidth - rect.right) + 'px';
            dropdown.style.width = '300px';
            dropdown.style.backgroundColor = 'white';
            dropdown.style.borderRadius = '5px';
            dropdown.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
            dropdown.style.zIndex = '1000';
            
            // Add styles for notification items
            const style = document.createElement('style');
            style.textContent = `
                .notification-dropdown {
                    font-family: 'Roboto', sans-serif;
                }
                .notification-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px;
                    border-bottom: 1px solid #eee;
                }
                .notification-header h3 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 500;
                }
                .mark-all-read {
                    color: #3498db;
                    text-decoration: none;
                    font-size: 14px;
                }
                .notification-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    max-height: 300px;
                    overflow-y: auto;
                }
                .notification-item {
                    display: flex;
                    padding: 15px;
                    border-bottom: 1px solid #eee;
                    transition: background-color 0.3s;
                }
                .notification-item:hover {
                    background-color: #f8f9fa;
                }
                .notification-item.unread {
                    background-color: #f0f7ff;
                }
                .notification-icon {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background-color: #f1f1f1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 15px;
                    color: #3498db;
                }
                .notification-content {
                    flex: 1;
                }
                .notification-content p {
                    margin: 0 0 5px;
                    font-size: 14px;
                }
                .notification-time {
                    color: #777;
                    font-size: 12px;
                }
                .notification-actions {
                    display: flex;
                    align-items: center;
                }
                .mark-read {
                    background: none;
                    border: none;
                    color: #3498db;
                    cursor: pointer;
                    padding: 5px;
                }
                .notification-footer {
                    padding: 15px;
                    text-align: center;
                    border-top: 1px solid #eee;
                }
                .notification-footer a {
                    color: #3498db;
                    text-decoration: none;
                    font-size: 14px;
                }
            `;
            document.head.appendChild(style);
            
            // Add to DOM
            document.body.appendChild(dropdown);
            
            // Close when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!dropdown.contains(e.target) && e.target !== notificationBell) {
                    document.body.removeChild(dropdown);
                    document.removeEventListener('click', closeDropdown);
                }
            });
            
            // Mark as read functionality
            const markReadButtons = dropdown.querySelectorAll('.mark-read');
            markReadButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const item = this.closest('.notification-item');
                    item.classList.remove('unread');
                    
                    // Update notification count
                    const count = document.querySelector('.notification-count');
                    if (count) {
                        const currentCount = parseInt(count.textContent);
                        count.textContent = currentCount - 1;
                        
                        if (currentCount - 1 <= 0) {
                            count.style.display = 'none';
                        }
                    }
                });
            });
            
            // Mark all as read
            const markAllReadButton = dropdown.querySelector('.mark-all-read');
            if (markAllReadButton) {
                markAllReadButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const unreadItems = dropdown.querySelectorAll('.notification-item.unread');
                    unreadItems.forEach(item => {
                        item.classList.remove('unread');
                    });
                    
                    // Update notification count
                    const count = document.querySelector('.notification-count');
                    if (count) {
                        count.textContent = '0';
                        count.style.display = 'none';
                    }
                });
            }
        });
    }
}

/**
 * Dropdown Menus
 */
function initializeDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const dropdown = this.nextElementSibling;
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            } else {
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
                
                dropdown.classList.add('show');
            }
            
            // Close when clicking outside
            document.addEventListener('click', function closeMenu(e) {
                if (!dropdown.contains(e.target) && e.target !== toggle) {
                    dropdown.classList.remove('show');
                    document.removeEventListener('click', closeMenu);
                }
            });
        });
    });
}

/**
 * Modal Dialogs
 */
function initializeModals() {
    const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            
            const modalId = this.getAttribute('data-target');
            const modal = document.querySelector(modalId);
            
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Close modal when clicking on close button or outside
                const closeButtons = modal.querySelectorAll('.close-modal, .modal-close');
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                    });
                });
                
                // Close when clicking outside the modal content
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                });
            }
        });
    });
}

/**
 * Tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            
            // Position the tooltip
            const rect = this.getBoundingClientRect();
            tooltip.style.position = 'absolute';
            tooltip.style.top = (rect.top - 30 + window.scrollY) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2) + 'px';
            tooltip.style.transform = 'translateX(-50%)';
            tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            tooltip.style.color = 'white';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '3px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '1000';
            
            document.body.appendChild(tooltip);
            
            this.addEventListener('mouseleave', function() {
                document.body.removeChild(tooltip);
            });
        });
    });
}

/**
 * Date Pickers
 */
function initializeDatePickers() {
    // This is a simple implementation
    // For a real project, you might want to use a library like flatpickr or datepicker.js
    const datePickers = document.querySelectorAll('.date-picker');
    
    datePickers.forEach(picker => {
        // Make sure the input is of type date for native date picker
        if (picker.tagName === 'INPUT') {
            picker.type = 'date';
        }
    });
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Add error message if it doesn't exist
                    let errorMessage = field.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.textContent = 'This field is required';
                        errorMessage.style.color = '#e74c3c';
                        errorMessage.style.fontSize = '12px';
                        errorMessage.style.marginTop = '5px';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    
                    // Remove error message if it exists
                    const errorMessage = field.nextElementSibling;
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        field.parentNode.removeChild(errorMessage);
                    }
                }
            });
            
            // Validate email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            emailFields.forEach(field => {
                if (field.value.trim() && !emailRegex.test(field.value.trim())) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Add error message if it doesn't exist
                    let errorMessage = field.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.textContent = 'Please enter a valid email address';
                        errorMessage.style.color = '#e74c3c';
                        errorMessage.style.fontSize = '12px';
                        errorMessage.style.marginTop = '5px';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}
