<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Fetch data from database
$buildings = $conn->query("SELECT DISTINCT building_name FROM rooms ORDER BY building_name");
$services = $conn->query("SELECT * FROM services ORDER BY Service_type");
$equipment = $conn->query("SELECT * FROM equipmentfacility ORDER BY EFname");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Concerns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            margin-top: 25px;
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

        .form-label.optional::after {
            content: " (Optional)";
            color: #6c757d;
            font-weight: normal;
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

        /* Equipment Dropdown Checklist */
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
            overflow-y: auto;
            display: none;
            padding: 0;
        }

        .equipment-dropdown .form-check {
            margin-bottom: 0;
            margin-left: 20px;
            padding: 10px 12px;
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

        /* Disabled state for room select */
        .custom-select.disabled .select-selected {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Alert Messages */
        .alert {
            margin: 15px;
            border-radius: 8px;
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
                <label for="title" class="form-label">Concern Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required 
                       placeholder="Enter a brief title for your concern">
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="3" required 
                          placeholder="Describe your concern in detail"></textarea>
            </div>

            <div class="row">
                <!-- Building selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Building <span class="text-danger">*</span></label>
                    <div class="custom-select" id="buildingSelect">
                        <div class="select-selected placeholder">Select a building</div>
                        <div class="select-items">
                            <?php while($building = $buildings->fetch_assoc()): ?>
                                <div data-value="<?php echo htmlspecialchars($building['building_name']); ?>">
                                    <?php echo htmlspecialchars($building['building_name']); ?>
                                </div>
                            <?php endwhile; ?>
                            <div data-value="Other">Other</div>
                        </div>
                    </div>
                    <input type="hidden" name="building" id="buildingInput" required>

                    <!-- Other building input -->
                    <div class="other-container" id="otherBuildingContainer">
                        <input type="text" id="other_building" name="other_building" placeholder="Enter building name">
                    </div>
                </div>

                <!-- Room selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Room <span class="text-danger">*</span></label>
                    <div class="custom-select disabled" id="roomSelect">
                        <div class="select-selected placeholder">Select a building first</div>
                        <div class="select-items" id="roomOptions">
                            <!-- Rooms will be populated dynamically based on building selection -->
                        </div>
                    </div>
                    <input type="hidden" name="room" id="roomInput" required>

                    <!-- Other room input -->
                    <div class="other-container" id="otherRoomContainer">
                        <input type="text" id="other_room" name="other_room" placeholder="Enter room name">
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Service Type selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Service Type <span class="text-danger">*</span></label>
                    <div class="custom-select" id="serviceSelect">
                        <div class="select-selected placeholder">Select service type</div>
                        <div class="select-items">
                            <?php while($service = $services->fetch_assoc()): ?>
                                <div data-value="<?php echo htmlspecialchars($service['Service_type']); ?>">
                                    <?php echo htmlspecialchars($service['Service_type']); ?>
                                </div>
                            <?php endwhile; ?>
                            <div data-value="Other">Other</div>
                        </div>
                    </div>
                    <input type="hidden" name="Service_type" id="serviceInput" required>

                    <!-- Other service input -->
                    <div class="other-container" id="otherServiceContainer">
                        <input type="text" id="other_service" name="other_service" placeholder="Enter service type">
                    </div>
                </div>

                <!-- Equipment / Facility selection - Dropdown checklist -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Equipment / Facility <span class="text-danger">*</span></label>
                    <div class="equipment-dropdown" id="equipmentSelect">
                        <div class="select-selected placeholder">Select equipment/facility</div>
                        <div class="select-items">
                            <?php 
                            // Reset pointer and fetch equipment again
                            $equipment->data_seek(0);
                            while($equip = $equipment->fetch_assoc()): 
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment[]" value="<?php echo htmlspecialchars($equip['EFname']); ?>" id="equip_<?php echo $equip['EFID']; ?>">
                                    <label class="form-check-label" for="equip_<?php echo $equip['EFID']; ?>">
                                        <?php echo htmlspecialchars($equip['EFname']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
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

            <!-- File attachment -->
            <div class="mb-3">
                <label for="attachment" class="form-label optional">Attachment (Photo/Video)</label>
                <input type="file" class="form-control" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.gif,.mp4,.mov">
                <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, GIF, MP4, MOV. (Optional)</small>
            </div>

            <!-- Submit button -->
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane me-2"></i>Submit Concern
            </button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the referrer (previous page)
    const referrer = document.referrer;
    const currentPage = window.location.href;
    
    // Only store the referrer if it's different from current page and from our domain
    if (referrer && 
        !referrer.includes(currentPage) && 
        (referrer.includes('userdb.php') || referrer.includes('userconcerns.php'))) {
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

    // Initialize all dropdowns
    initializeDropdowns();
});

function initializeDropdowns() {
    // Initialize Building Select
    const buildingSelect = document.getElementById('buildingSelect');
    const buildingSelected = buildingSelect.querySelector('.select-selected');
    const buildingItems = buildingSelect.querySelector('.select-items');
    const buildingInput = document.getElementById('buildingInput');
    const otherBuildingContainer = document.getElementById('otherBuildingContainer');

    buildingSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        closeAllDropdowns();
        buildingItems.style.display = buildingItems.style.display === 'block' ? 'none' : 'block';
    });

    buildingItems.querySelectorAll('div').forEach(option => {
        option.addEventListener('click', function() {
            buildingSelected.textContent = this.textContent;
            buildingSelected.classList.remove('placeholder');
            buildingInput.value = this.getAttribute('data-value');
            buildingItems.style.display = 'none';

            if (this.getAttribute('data-value') === 'Other') {
                otherBuildingContainer.style.height = '44px';
                otherBuildingContainer.querySelector('input').required = true;
            } else {
                otherBuildingContainer.style.height = '0';
                const input = otherBuildingContainer.querySelector('input');
                input.required = false;
                input.value = '';
            }

            // Load rooms for the selected building
            loadRoomsForBuilding(this.getAttribute('data-value'));
        });
    });

    // Initialize Service Select
    const serviceSelect = document.getElementById('serviceSelect');
    const serviceSelected = serviceSelect.querySelector('.select-selected');
    const serviceItems = serviceSelect.querySelector('.select-items');
    const serviceInput = document.getElementById('serviceInput');
    const otherServiceContainer = document.getElementById('otherServiceContainer');

    serviceSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        closeAllDropdowns();
        serviceItems.style.display = serviceItems.style.display === 'block' ? 'none' : 'block';
    });

    serviceItems.querySelectorAll('div').forEach(option => {
        option.addEventListener('click', function() {
            serviceSelected.textContent = this.textContent;
            serviceSelected.classList.remove('placeholder');
            serviceInput.value = this.getAttribute('data-value');
            serviceItems.style.display = 'none';

            if (this.getAttribute('data-value') === 'Other') {
                otherServiceContainer.style.height = '44px';
                otherServiceContainer.querySelector('input').required = true;
            } else {
                otherServiceContainer.style.height = '0';
                const input = otherServiceContainer.querySelector('input');
                input.required = false;
                input.value = '';
            }
        });
    });

    // Initialize Equipment Dropdown
    const equipmentSelect = document.getElementById('equipmentSelect');
    const equipmentSelected = equipmentSelect.querySelector('.select-selected');
    const equipmentItems = equipmentSelect.querySelector('.select-items');
    const otherEquipmentContainer = document.getElementById('otherEquipmentContainer');
    const otherEquipmentInput = document.getElementById('other_equipment');
    const selectedEquipmentDisplay = document.getElementById('selectedEquipment');

    equipmentSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        closeAllDropdowns();
        equipmentItems.style.display = equipmentItems.style.display === 'block' ? 'none' : 'block';
    });

    // Update selected equipment display
    function updateSelectedEquipment() {
        const checkboxes = document.querySelectorAll('input[name="equipment[]"]:checked');
        const selectedValues = Array.from(checkboxes).map(cb => {
            if (cb.value === 'Other') {
                const otherInput = document.getElementById('other_equipment');
                return otherInput.value ? otherInput.value : 'Other';
            }
            return cb.value;
        });
        
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

    // Also update when other equipment input changes
    otherEquipmentInput.addEventListener('input', updateSelectedEquipment);
}

// Load rooms for selected building
function loadRoomsForBuilding(buildingName) {
    const roomSelect = document.getElementById('roomSelect');
    const roomSelected = roomSelect.querySelector('.select-selected');
    const roomItems = document.getElementById('roomOptions');
    const roomInput = document.getElementById('roomInput');
    
    // Enable room select
    roomSelect.classList.remove('disabled');
    roomSelected.textContent = 'Loading rooms...';

    if (buildingName === 'Other') {
        // For "Other" building, show only "Other" option
        roomItems.innerHTML = '<div data-value="Other">Other</div>';
        initializeRoomSelect();
        roomSelected.textContent = 'Select a room';
        roomSelected.classList.add('placeholder');
        return;
    }

    // AJAX call to get rooms for the selected building
    fetch('get_rooms.php?building=' + encodeURIComponent(buildingName))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            roomItems.innerHTML = '';
            if (data.length > 0) {
                data.forEach(room => {
                    const option = document.createElement('div');
                    option.textContent = room.roomname;
                    option.setAttribute('data-value', room.roomname);
                    roomItems.appendChild(option);
                });
            }
            // Always add "Other" option
            roomItems.innerHTML += '<div data-value="Other">Other</div>';
            initializeRoomSelect();
            roomSelected.textContent = 'Select a room';
            roomSelected.classList.add('placeholder');
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            roomItems.innerHTML = '<div data-value="Other">Other</div>';
            initializeRoomSelect();
            roomSelected.textContent = 'Select a room';
            roomSelected.classList.add('placeholder');
        });
}

// Initialize room select after loading rooms
function initializeRoomSelect() {
    const roomSelect = document.getElementById('roomSelect');
    const roomSelected = roomSelect.querySelector('.select-selected');
    const roomItems = document.getElementById('roomOptions');
    const roomInput = document.getElementById('roomInput');
    const otherRoomContainer = document.getElementById('otherRoomContainer');

    roomSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        closeAllDropdowns();
        roomItems.style.display = roomItems.style.display === 'block' ? 'none' : 'block';
    });

    roomItems.querySelectorAll('div').forEach(option => {
        option.addEventListener('click', function() {
            roomSelected.textContent = this.textContent;
            roomSelected.classList.remove('placeholder');
            roomInput.value = this.getAttribute('data-value');
            roomItems.style.display = 'none';

            if (this.getAttribute('data-value') === 'Other') {
                otherRoomContainer.style.height = '44px';
                otherRoomContainer.querySelector('input').required = true;
            } else {
                otherRoomContainer.style.height = '0';
                const input = otherRoomContainer.querySelector('input');
                input.required = false;
                input.value = '';
            }
        });
    });
}

// Function to close all dropdowns
function closeAllDropdowns() {
    const allSelects = ['buildingSelect', 'roomSelect', 'serviceSelect', 'equipmentSelect'];
    
    allSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const items = select.querySelector('.select-items');
            if (items) {
                items.style.display = 'none';
            }
        }
    });
}

// Close dropdowns when clicking elsewhere
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select') && !e.target.closest('.equipment-dropdown')) {
        closeAllDropdowns();
    }
});

// Reset all custom dropdowns to initial state
function resetCustomDropdowns() {
    // Reset building dropdown
    const buildingSelected = document.querySelector('#buildingSelect .select-selected');
    buildingSelected.textContent = 'Select a building';
    buildingSelected.classList.add('placeholder');
    document.getElementById('buildingInput').value = '';
    document.getElementById('otherBuildingContainer').style.height = '0';
    document.getElementById('other_building').value = '';

    // Reset room dropdown
    const roomSelected = document.querySelector('#roomSelect .select-selected');
    roomSelected.textContent = 'Select a building first';
    roomSelected.classList.add('placeholder');
    document.getElementById('roomInput').value = '';
    document.getElementById('otherRoomContainer').style.height = '0';
    document.getElementById('other_room').value = '';
    document.getElementById('roomSelect').classList.add('disabled');

    // Reset service dropdown
    const serviceSelected = document.querySelector('#serviceSelect .select-selected');
    serviceSelected.textContent = 'Select service type';
    serviceSelected.classList.add('placeholder');
    document.getElementById('serviceInput').value = '';
    document.getElementById('otherServiceContainer').style.height = '0';
    document.getElementById('other_service').value = '';

    // Reset equipment dropdown
    const equipmentSelected = document.querySelector('#equipmentSelect .select-selected');
    equipmentSelected.textContent = 'Select equipment/facility';
    equipmentSelected.classList.add('placeholder');
    document.getElementById('selectedEquipment').textContent = '';
    
    // Uncheck all equipment checkboxes
    document.querySelectorAll('input[name="equipment[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    document.getElementById('otherEquipmentContainer').style.height = '0';
    document.getElementById('other_equipment').value = '';

    // Reset file input
    document.getElementById('attachment').value = '';

    // Reset border styles if they were highlighted as errors
    document.querySelectorAll('.custom-select .select-selected, .equipment-dropdown .select-selected').forEach(element => {
        element.style.border = '1px solid #ced4da';
    });
    
    document.getElementById('title').style.border = '1px solid #ced4da';
    document.getElementById('description').style.border = '1px solid #ced4da';
}

// Form validation and submission
const form = document.getElementById('concernForm');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    let valid = true;

    // Validate required inputs
    ['title','description'].forEach(id => {
        const input = document.getElementById(id);
        if (!input.value) {
            valid = false;
            input.style.border = '2px solid #dc3545';
        } else {
            input.style.border = '1px solid #ced4da';
        }
    });

    // Validate dropdown selections
    ['buildingInput','roomInput','serviceInput'].forEach(id => {
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
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill out all required fields before submitting.',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;

    // Create FormData object
    const formData = new FormData(form);

    // Submit the form via fetch
    fetch('usersubmit_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonText: 'OK'
            }).then(() => {
                // Get the redirect URL from sessionStorage or use default
                const previousPage = sessionStorage.getItem('previousPage');
                let redirectUrl = 'userconcerns.php'; // Default fallback
                
                if (previousPage) {
                    redirectUrl = previousPage;
                }
                
                // Redirect to the appropriate page
                window.location.href = redirectUrl;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Something went wrong. Please try again.',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// File size validation (only if file is selected)
document.getElementById('attachment').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.size > 5 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'File Too Large',
            text: 'File size must be less than 5MB.',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
        e.target.value = '';
    }
});
</script>
</body>
</html>