<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Concerns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            background: #f9fafb;
        }

        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #087830, #3c4142);
            padding: 15px 15px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }

        .logo img {
            height: 35px;
            width: auto;
            object-fit: contain;
        }

        .navbar h2 {
            font-size: 24px;
            margin-left: 50px;
            margin-top: 2px;
        }

        .return-btn {
            background: #107040;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
            font-size: 14px;
            margin-left: auto;
        }

        .return-btn:hover {
            background: #07532e;
            color: white;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin: 0 auto 30px;
            max-width: 850px;
            width: 100%;
            box-sizing: border-box;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #0c3c2f, #116546);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: linear-gradient(90deg, #116546, #0c3c2f);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: bold;
            color: #163a37;
            margin-bottom: 8px;
        }

        .custom-select {
            position: relative;
            user-select: none;
            width: 100%;
        }

        .custom-select .select-selected {
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 44px;
            display: flex;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
            transition: border 0.3s;
        }

        .custom-select .select-selected.placeholder {
            color: #6c757d;
        }

        .custom-select .select-items {
            position: absolute;
            background-color: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 6px 6px;
            z-index: 99;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .custom-select .select-items div {
            padding: 10px 12px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            align-items: center;
        }

        .custom-select .select-items div:hover {
            background-color: #f8f9fa;
        }

        .custom-select .select-items div:last-child {
            border-bottom: none;
        }

        /* Equipment Dropdown Checklist - REMOVED EXTRA SCROLLBAR */
        .equipment-dropdown {
            position: relative;
            user-select: none;
            width: 100%;
        }

        .equipment-dropdown .select-selected {
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 44px;
            display: flex;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
            transition: border 0.3s;
        }

        .equipment-dropdown .select-selected.placeholder {
            color: #6c757d;
        }

        .equipment-dropdown .select-items {
            position: absolute;
            background-color: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 6px 6px;
            z-index: 99;
            width: 100%;
            max-height: 200px;
            overflow-y: auto; /* Only one scrollbar here */
            display: none;
            padding: 0; /* Remove padding to prevent double scrollbar */
        }

        /* Remove the nested equipment-checklist scrollbar */
        .equipment-dropdown .equipment-checklist {
            background: white;
            /* Remove max-height and overflow-y to prevent double scrollbar */
        }

        .equipment-dropdown .form-check {
            margin-bottom: 0;
            margin-left: 20px; /* Reduce margin to fit more items */
            padding: 10px 12px; /* Consistent padding */
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s;
        }

        .equipment-dropdown .form-check:last-child {
            border-bottom: none;
        }

        .equipment-dropdown .form-check:hover {
            background-color: #e9ecef;
        }

        .equipment-dropdown .form-check-input {
            margin-right: 10px;
        }

        .equipment-dropdown .form-check-label {
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 0;
        }

        /* Selected Equipment Display */
        .selected-equipment {
            margin-top: 8px;
            font-size: 13px;
            color: #6c757d;
            max-height: 60px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 4px;
            padding: 6px 10px;
        }

        /* Other Container */
        .other-container {
            overflow: hidden;
            height: 0;
            transition: height 0.3s ease;
            margin-top: 8px;
        }

        .other-container input {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            height: 44px;
        }

        /* File Input */
        .form-control[type="file"] {
            padding: 8px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 12px 15px;
                flex-wrap: wrap;
            }
            
            .logo {
                margin-right: 10px;
            }
            
            .navbar h2 {
                font-size: 16px;
                margin-left: 20px;
                margin-top: 10px;
            }
            
            .return-btn {
                padding: 5px 10px;
                font-size: 13px;
            }
            
            .form-card {
                padding: 20px;
                margin: 0 10px 20px;
            }
            
            .equipment-dropdown .select-items {
                max-height: 150px;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                padding: 10px 12px;
            }
            
            .logo img {
                height: 35px;
            }
            
            .navbar h2 {
                font-size: 15px;
                margin-left: 10px;
            }
            
            .form-card {
                padding: 15px;
            }
            
            .submit-btn {
                padding: 10px;
                font-size: 15px;
            }
            
            .custom-select .select-selected,
            .equipment-dropdown .select-selected,
            .other-container input,
            .form-control {
                min-height: 40px;
                padding: 8px 10px;
                font-size: 13px;
            }
            
            .return-btn {
                padding: 4px 8px;
                font-size: 12px;
            }
            
            .equipment-dropdown .select-items {
                max-height: 120px;
            }
            
            .equipment-dropdown .form-check {
                padding: 8px 10px;
            }
        }

        @media (max-width: 400px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .logo {
                justify-content: center;
                margin-right: 0;
            }
            
            .navbar h2 {
                margin-left: 0;
            }
            
            .return-btn {
                width: auto;
                margin-left: 0;
            }
            
            .form-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Submit New Concerns</h2>
    </div>

    <a href="#" id="returnButton" class="return-btn">
        <i class="fas fa-arrow-left me-1"></i> Return
    </a>
</div>

<!-- Form -->
<div class="container">
    <div class="form-card">
        <form id="concernForm" action="usersubmit_process.php" method="POST" enctype="multipart/form-data">

            <!-- Concern Title -->
            <div class="mb-3">
                <label for="title" class="form-label">Concern Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>

            <div class="row">
                <!-- Room selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Room</label>
                    <div class="custom-select" id="roomSelect">
                        <div class="select-selected placeholder">Select a room</div>
                        <div class="select-items">
                            <div data-value="LS 211">LS 211</div>
                            <div data-value="LS 212">LS 212</div>
                            <div data-value="LS 213">LS 213</div>
                            <div data-value="SB 311">SB 311</div>
                            <div data-value="SB 312">SB 312</div>
                            <div data-value="SB 313">SB 313</div>
                            <div data-value="Other">Other</div>
                        </div>
                    </div>
                    <input type="hidden" name="room" id="roomInput" required>

                    <!-- Other room input -->
                    <div class="other-container" id="otherRoomContainer">
                        <input type="text" id="other_room" name="other_room" placeholder="Enter room name">
                    </div>
                </div>

                <!-- Equipment / Facility selection - Dropdown checklist -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Equipment / Facility</label>
                    <div class="equipment-dropdown" id="equipmentSelect">
                        <div class="select-selected placeholder">Select equipment/facility</div>
                        <div class="select-items">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Air Conditioner" id="equip1">
                                <label class="form-check-label" for="equip1">Air Conditioner</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Electric Fan" id="equip2">
                                <label class="form-check-label" for="equip2">Electric Fan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Chair" id="equip3">
                                <label class="form-check-label" for="equip3">Chair</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Lights" id="equip4">
                                <label class="form-check-label" for="equip4">Lights</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Outlet" id="equip5">
                                <label class="form-check-label" for="equip5">Outlet</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Projector" id="equip6">
                                <label class="form-check-label" for="equip6">Projector</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Whiteboard" id="equip7">
                                <label class="form-check-label" for="equip7">Whiteboard</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Table" id="equip8">
                                <label class="form-check-label" for="equip8">Table</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Computer" id="equip9">
                                <label class="form-check-label" for="equip9">Computer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Printer" id="equip10">
                                <label class="form-check-label" for="equip10">Printer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Window" id="equip11">
                                <label class="form-check-label" for="equip11">Window</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Door" id="equip12">
                                <label class="form-check-label" for="equip12">Door</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment[]" value="Other" id="equipOther">
                                <label class="form-check-label" for="equipOther">Other</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selected equipment display -->
                    <div class="selected-equipment" id="selectedEquipment"></div>
                    
                    <!-- Other equipment input -->
                    <div class="other-container" id="otherEquipmentContainer">
                        <input type="text" id="other_equipment" name="other_equipment" placeholder="Enter equipment/facility name">
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Problem Type selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Problem Type</label>
                    <div class="custom-select" id="problemSelect">
                        <div class="select-selected placeholder">Select problem type</div>
                        <div class="select-items">
                            <div data-value="Carpentry">Carpentry</div>
                            <div data-value="Masonry">Masonry</div>
                            <div data-value="Electrical">Electrical</div>
                            <div data-value="Plumbing">Plumbing</div>
                            <div data-value="Painting">Painting</div>
                            <div data-value="Fabrication">Fabrication</div>
                            <div data-value="Appliance Repair/Installation">Appliance Repair/Installation</div>
                        </div>
                    </div>
                    <input type="hidden" name="problem_type" id="problemInput" required>
                </div>

                <!-- Priority selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Priority</label>
                    <div class="custom-select" id="prioritySelect">
                        <div class="select-selected placeholder">Select priority</div>
                        <div class="select-items">
                            <div data-value="Low">Low</div>
                            <div data-value="Medium">Medium</div>
                            <div data-value="High">High</div>
                            <div data-value="Urgent">Urgent</div>
                        </div>
                    </div>
                    <input type="hidden" name="priority" id="priorityInput" required>
                </div>
            </div>

            <!-- File attachment -->
            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment (Photo/Video)</label>
                <input type="file" class="form-control" id="attachment" name="attachment" accept=".jpg,.png,.gif,.mp4,.mov" required>
                <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, GIF, MP4, MOV.</small>
            </div>

            <!-- Submit button -->
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane me-2"></i>Submit Concern
            </button>
        </form>
    </div>
</div>

<script>
// Store the referrer URL when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get the referrer (previous page)
    const referrer = document.referrer;
    
    // Store it in sessionStorage for persistence
    if (referrer && !referrer.includes('usersubmit.php')) {
        sessionStorage.setItem('previousPage', referrer);
    }
    
    // Set the return button href
    const returnButton = document.getElementById('returnButton');
    const previousPage = sessionStorage.getItem('previousPage');
    
    if (previousPage) {
        returnButton.href = previousPage;
    } else {
        // Fallback to userconcerns.php if no referrer is available
        returnButton.href = 'userconcerns.php';
    }
});

// Function to close all dropdowns except the specified one
function closeAllDropdowns(exceptSelectId = null) {
    const allSelects = ['roomSelect', 'equipmentSelect', 'problemSelect', 'prioritySelect'];
    
    allSelects.forEach(selectId => {
        if (selectId !== exceptSelectId) {
            const select = document.getElementById(selectId);
            if (select) {
                const items = select.querySelector('.select-items');
                if (items) {
                    items.style.display = 'none';
                }
            }
        }
    });
}

// Custom select functionality
function initCustomSelect(selectId, hiddenInputId, otherContainerId) {
    const select = document.getElementById(selectId);
    const selected = select.querySelector('.select-selected');
    const items = select.querySelector('.select-items');
    const hiddenInput = hiddenInputId ? document.getElementById(hiddenInputId) : null;
    const otherContainer = otherContainerId ? document.getElementById(otherContainerId) : null;

    selected.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Close all other dropdowns first
        closeAllDropdowns(selectId);
        
        // Toggle current dropdown
        items.style.display = items.style.display === 'block' ? 'none' : 'block';
    });

    // For equipment dropdown, we don't have individual options to click
    if (selectId !== 'equipmentSelect') {
        const options = items.querySelectorAll('div');
        options.forEach(option => {
            option.addEventListener('click', () => {
                selected.textContent = option.textContent;
                selected.classList.remove('placeholder');
                if (hiddenInput) hiddenInput.value = option.dataset.value;
                items.style.display = 'none';

                if (option.dataset.value === 'Other' && otherContainer) {
                    otherContainer.style.height = '44px';
                    otherContainer.querySelector('input').required = true;
                } else if (otherContainer) {
                    otherContainer.style.height = '0';
                    const input = otherContainer.querySelector('input');
                    input.required = false;
                    input.value = '';
                }
            });
        });
    }

    // Close dropdown when clicking elsewhere
    document.addEventListener('click', (e) => {
        if (!select.contains(e.target)) {
            items.style.display = 'none';
        }
    });
}

// Initialize all custom selects
initCustomSelect('roomSelect', 'roomInput', 'otherRoomContainer');
initCustomSelect('equipmentSelect');
initCustomSelect('problemSelect', 'problemInput');
initCustomSelect('prioritySelect', 'priorityInput');

// Equipment checklist functionality
const equipOtherCheckbox = document.getElementById('equipOther');
const otherEquipmentContainer = document.getElementById('otherEquipmentContainer');
const otherEquipmentInput = document.getElementById('other_equipment');
const selectedEquipmentDisplay = document.getElementById('selectedEquipment');
const equipmentSelected = document.querySelector('#equipmentSelect .select-selected');

// Update selected equipment display
function updateSelectedEquipment() {
    const checkboxes = document.querySelectorAll('input[name="equipment[]"]:checked');
    const selectedValues = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedValues.length > 0) {
        selectedEquipmentDisplay.textContent = 'Selected: ' + selectedValues.join(', ');
        equipmentSelected.textContent = selectedValues.length + ' item(s) selected';
        equipmentSelected.classList.remove('placeholder');
    } else {
        selectedEquipmentDisplay.textContent = '';
        equipmentSelected.textContent = 'Select equipment/facility';
        equipmentSelected.classList.add('placeholder');
    }
}

// Add event listeners to all equipment checkboxes
document.querySelectorAll('input[name="equipment[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.value === 'Other') {
            if (this.checked) {
                otherEquipmentContainer.style.height = '44px';
                otherEquipmentInput.required = true;
            } else {
                otherEquipmentContainer.style.height = '0';
                otherEquipmentInput.required = false;
                otherEquipmentInput.value = '';
            }
        }
        updateSelectedEquipment();
    });
});

// Close dropdowns when clicking on form inputs
document.addEventListener('click', function(e) {
    // If clicking on any form input that's not a custom select, close all dropdowns
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        const isCustomSelect = e.target.closest('.custom-select') || e.target.closest('.equipment-dropdown');
        if (!isCustomSelect) {
            closeAllDropdowns();
        }
    }
});

// Form validation
const form = document.getElementById('concernForm');
form.addEventListener('submit', function(e) {
    let valid = true;

    // Validate required inputs
    ['title','description','attachment'].forEach(id => {
        const input = document.getElementById(id);
        if (!input.value) {
            valid = false;
            input.style.border = '2px solid #dc3545';
        } else {
            input.style.border = '1px solid #ced4da';
        }
    });

    // Validate dropdown selections
    ['roomInput','problemInput','priorityInput'].forEach(id => {
        const input = document.getElementById(id);
        const customSelect = input.previousElementSibling || input.parentElement.querySelector('.select-selected');
        if (!input.value) {
            valid = false;
            customSelect.style.border = '2px solid #dc3545';
        } else {
            customSelect.style.border = '1px solid #ced4da';
        }
    });

    // Validate at least one equipment is selected
    const equipmentCheckboxes = document.querySelectorAll('input[name="equipment[]"]');
    const equipmentSelected = Array.from(equipmentCheckboxes).some(cb => cb.checked);
    if (!equipmentSelected) {
        valid = false;
        document.querySelector('#equipmentSelect .select-selected').style.border = '2px solid #dc3545';
    } else {
        document.querySelector('#equipmentSelect .select-selected').style.border = '1px solid #ced4da';
    }

    if (!valid) {
        e.preventDefault();
        alert('Please fill out all required fields before submitting.');
    }
});

// File size validation
document.getElementById('attachment').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB.');
        e.target.value = '';
    }
});
</script>
</body>
</html>