// Main JavaScript file for Vet Anywhere application

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize user dropdown menu
    initUserDropdown();
    
    // Initialize notifications dropdown
    initNotificationsDropdown();
    
    // Initialize modals
    initModals();
    
    // Initialize tooltips
    initTooltips();
    
    // Setup date formatting
    setupDateFormatting();
    
    // Setup form validation
    setupFormValidation();

    // Identify form fields that should show validation feedback
    const formInputs = document.querySelectorAll('.form-control[required]');
    
    formInputs.forEach(input => {
        // Create validation feedback element
        const feedbackEl = document.createElement('div');
        feedbackEl.className = 'validation-feedback';
        input.parentElement.appendChild(feedbackEl);
        
        // Real-time validation
        input.addEventListener('input', function() {
            validateField(this);
        });
        
        input.addEventListener('blur', function() {
            validateField(this, true);
        });
    });
    
    function validateField(field, isBlur = false) {
        const feedbackEl = field.parentElement.querySelector('.validation-feedback');
        const fieldType = field.getAttribute('type');
        const fieldValue = field.value.trim();
        const fieldName = field.getAttribute('placeholder') || 'Field';
        const formGroup = field.closest('.form-floating');
        
        // Clear previous feedback
        feedbackEl.textContent = '';
        feedbackEl.className = 'validation-feedback';
        
        // Don't show validation messages until user has interacted with field
        if (fieldValue === '' && !isBlur) return;
        
        // Field-specific validations
        if (fieldValue === '') {
            feedbackEl.textContent = `${fieldName} is required`;
            feedbackEl.classList.add('error-message');
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
            return;
        }
        
        if (fieldType === 'email' && !isValidEmail(fieldValue)) {
            feedbackEl.textContent = 'Please enter a valid email address';
            feedbackEl.classList.add('error-message');
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
            return;
        }
        
        if (fieldType === 'password' && fieldValue.length < 8) {
            feedbackEl.textContent = 'Password must be at least 8 characters';
            feedbackEl.classList.add('error-message');
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
            return;
        }
        
        if (field.id === 'confirm_password') {
            const password = document.getElementById('password').value;
            if (fieldValue !== password) {
                feedbackEl.textContent = 'Passwords do not match';
                feedbackEl.classList.add('error-message');
                formGroup.classList.add('error');
                formGroup.classList.remove('success');
                return;
            }
        }
        
        if (fieldType === 'tel' && !isValidPhone(fieldValue)) {
            feedbackEl.textContent = 'Please enter a valid phone number';
            feedbackEl.classList.add('error-message');
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
            return;
        }
        
        // Field is valid
        formGroup.classList.remove('error');
        formGroup.classList.add('success');
        
        if (isBlur) {
            feedbackEl.textContent = 'Looks good!';
            feedbackEl.classList.add('success-message');
        }
    }
    
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email.toLowerCase());
    }
    
    function isValidPhone(phone) {
        // Basic phone validation - can be customized for your country format
        return phone.length >= 10;
    }
    
    // Form submission with animation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't add animation if form is invalid
            if (!this.checkValidity()) {
                e.preventDefault();
                return;
            }
            
            // Add loading state to button
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                submitBtn.disabled = true;
            }
        });
    });
});

/**
 * Initialize user dropdown menu
 */
function initUserDropdown() {
    const userProfile = document.querySelector('.user-profile');
    const dropdownMenu = document.querySelector('.user-dropdown');
    
    if (userProfile && dropdownMenu) {
        userProfile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownMenu.contains(e.target) && !userProfile.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
}

/**
 * Initialize notifications dropdown
 */
function initNotificationsDropdown() {
    const notificationsButton = document.querySelector('.notifications-button');
    const notificationsDropdown = document.querySelector('.notifications-dropdown');
    
    if (notificationsButton && notificationsDropdown) {
        notificationsButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationsDropdown.classList.toggle('show');
            
            // Mark notifications as read
            if (notificationsDropdown.classList.contains('show')) {
                markNotificationsAsRead();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationsDropdown.contains(e.target) && !notificationsButton.contains(e.target)) {
                notificationsDropdown.classList.remove('show');
            }
        });
    }
}

/**
 * Mark notifications as read
 */
function markNotificationsAsRead() {
    const badge = document.querySelector('.notification-badge');
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    
    if (unreadItems.length > 0) {
        // Send AJAX request to mark notifications as read
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../includes/mark_notifications_read.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Success: Update UI
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                });
                
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        };
        xhr.send();
    }
}

/**
 * Initialize modal functionality
 */
function initModals() {
    // Get all modal triggers
    const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target');
            const modal = document.querySelector(targetId);
            
            if (modal) {
                const backdrop = modal.querySelector('.modal-backdrop');
                backdrop.classList.add('show');
                
                // Close modal when clicking on close button
                const closeButton = modal.querySelector('.modal-close');
                if (closeButton) {
                    closeButton.addEventListener('click', function() {
                        backdrop.classList.remove('show');
                    });
                }
                
                // Close modal when clicking on backdrop
                backdrop.addEventListener('click', function(e) {
                    if (e.target === backdrop) {
                        backdrop.classList.remove('show');
                    }
                });
            }
        });
    });
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            
            // Create tooltip element
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = text;
            
            // Append to body
            document.body.appendChild(tooltipEl);
            
            // Position the tooltip
            const rect = this.getBoundingClientRect();
            tooltipEl.style.top = (rect.top - tooltipEl.offsetHeight - 10) + 'px';
            tooltipEl.style.left = (rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2)) + 'px';
            
            // Show the tooltip
            setTimeout(() => {
                tooltipEl.style.opacity = '1';
                tooltipEl.style.transform = 'translateY(0)';
            }, 10);
            
            // Store tooltip element reference
            this.tooltipElement = tooltipEl;
        });
        
        tooltip.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                const tooltipEl = this.tooltipElement;
                
                // Fade out tooltip
                tooltipEl.style.opacity = '0';
                tooltipEl.style.transform = 'translateY(-10px)';
                
                // Remove tooltip after animation
                setTimeout(() => {
                    if (tooltipEl.parentNode) {
                        tooltipEl.parentNode.removeChild(tooltipEl);
                    }
                }, 300);
            }
        });
    });
}

/**
 * Setup date formatting
 */
function setupDateFormatting() {
    const dateElements = document.querySelectorAll('.date-format');
    
    dateElements.forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        if (timestamp) {
            const date = new Date(parseInt(timestamp) * 1000);
            element.textContent = formatDate(date);
        }
    });
}

/**
 * Format date
 * @param {Date} date The date to format
 * @returns {string} Formatted date string
 */
function formatDate(date) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
}

/**
 * Show loading spinner
 * @param {HTMLElement} targetElement Element where spinner should be shown
 */
function showSpinner(targetElement) {
    // Save original content
    targetElement.dataset.originalContent = targetElement.innerHTML;
    
    // Create and show spinner
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    targetElement.innerHTML = '';
    targetElement.appendChild(spinner);
    targetElement.disabled = true;
}

/**
 * Hide loading spinner
 * @param {HTMLElement} targetElement Element where spinner is shown
 */
function hideSpinner(targetElement) {
    // Restore original content
    if (targetElement.dataset.originalContent) {
        targetElement.innerHTML = targetElement.dataset.originalContent;
        targetElement.disabled = false;
    }
}

/**
 * Format currency
 * @param {number} amount Amount to format
 * @param {string} currency Currency code (default: USD)
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Create a calendar for the given month and year
 * @param {number} month Month (0-11)
 * @param {number} year Year
 * @param {Array} events Array of event objects with date property
 * @param {HTMLElement} containerElement Container to render calendar in
 */
function createCalendar(month, year, events, containerElement) {
    const today = new Date();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDay = firstDay.getDay(); // 0: Sunday, 1: Monday, etc.
    
    // Clear container
    containerElement.innerHTML = '';
    
    // Create day headers
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayNames.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day-name';
        dayElement.textContent = day;
        containerElement.appendChild(dayElement);
    });
    
    // Create empty cells for days before the first day of the month
    for (let i = 0; i < startingDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day other-month';
        containerElement.appendChild(emptyDay);
    }
    
    // Create cells for each day in the month
    for (let i = 1; i <= daysInMonth; i++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        // Add day number
        const dayNumber = document.createElement('div');
        dayNumber.className = 'calendar-day-number';
        dayNumber.textContent = i;
        dayElement.appendChild(dayNumber);
        
        // Check if current day
        const currentDate = new Date(year, month, i);
        if (currentDate.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        // Check for events on this day
        if (events && events.length > 0) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const dayEvents = events.filter(event => {
                const eventDate = new Date(event.date);
                return eventDate.getFullYear() === year && 
                       eventDate.getMonth() === month && 
                       eventDate.getDate() === i;
            });
            
            if (dayEvents.length > 0) {
                dayElement.classList.add('has-events');
                
                // Add event indicators
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicators';
                
                for (let j = 0; j < Math.min(dayEvents.length, 3); j++) {
                    const dot = document.createElement('span');
                    dot.className = 'event-dot';
                    eventIndicator.appendChild(dot);
                }
                
                if (dayEvents.length > 3) {
                    const more = document.createElement('small');
                    more.textContent = `+${dayEvents.length - 3}`;
                    eventIndicator.appendChild(more);
                }
                
                dayElement.appendChild(eventIndicator);
                
                // Make clickable to show events
                dayElement.style.cursor = 'pointer';
                dayElement.setAttribute('data-date', dateStr);
                dayElement.addEventListener('click', function() {
                    showEventsForDate(this.getAttribute('data-date'), dayEvents);
                });
            }
        }
        
        containerElement.appendChild(dayElement);
    }
    
    // Add empty cells for days after the last day of the month to complete the grid
    const totalCells = 42; // 6 rows of 7 days
    const remainingCells = totalCells - (startingDay + daysInMonth);
    for (let i = 0; i < remainingCells; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day other-month';
        containerElement.appendChild(emptyDay);
    }
}

/**
 * Show events for a specific date
 * @param {string} date Date in YYYY-MM-DD format
 * @param {Array} events Array of event objects
 */
function showEventsForDate(date, events) {
    // Create modal to display events
    const modalHTML = `
        <div class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Events for ${formatDateString(date)}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <ul class="event-list">
                        ${events.map(event => `
                            <li class="event-item">
                                <div class="event-time">${formatTime(new Date(event.date))}</div>
                                <div class="event-title">${event.title}</div>
                                <div class="event-details">${event.details || ''}</div>
                            </li>
                        `).join('')}
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-close">Close</button>
                </div>
            </div>
        </div>
    `;
    
    // Append modal to body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Show modal
    setTimeout(() => {
        modalContainer.querySelector('.modal-backdrop').classList.add('show');
    }, 10);
    
    // Setup close buttons
    const closeButtons = modalContainer.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modalContainer.querySelector('.modal-backdrop').classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(modalContainer);
            }, 300);
        });
    });
    
    // Close when clicking outside
    modalContainer.querySelector('.modal-backdrop').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(modalContainer);
            }, 300);
        }
    });
}

/**
 * Format date string for display
 * @param {string} dateStr Date in YYYY-MM-DD format
 * @returns {string} Formatted date string
 */
function formatDateString(dateStr) {
    const date = new Date(dateStr);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Format time for display
 * @param {Date} date Date object
 * @returns {string} Formatted time string
 */
function formatTime(date) {
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}