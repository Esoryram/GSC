<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #087830, #3c4142); color:white;">
        <h5 class="modal-title" id="changePasswordLabel">
            <i class="fas fa-key me-2"></i>Change Password
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="changePasswordForm">
          <div class="mb-3">
            <label for="currentPassword" class="form-label">Current Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="currentPassword" required>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="currentPassword" style="display: none;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label for="newPassword" class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="newPassword" required>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newPassword" style="display: none;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirmPassword" required>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword" style="display: none;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="savePasswordBtn">
            <i class="fas fa-save me-1"></i>Change Password
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Password visibility toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle password visibility
    function togglePasswordVisibility(button) {
        const targetId = button.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const icon = button.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            button.title = 'Hide password';
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            button.title = 'Show password';
        }
        
        // Focus back on the input for better UX
        passwordInput.focus();
    }

    // Function to handle input events and show/hide eye icon
    function handlePasswordInput(input) {
        const targetId = input.id;
        const toggleButton = document.querySelector(`.toggle-password[data-target="${targetId}"]`);
        
        if (input.value.length > 0) {
            toggleButton.style.display = 'block';
        } else {
            toggleButton.style.display = 'none';
            // Reset to password type and eye icon when empty
            input.type = 'password';
            const icon = toggleButton.querySelector('i');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Initialize event listeners for all password inputs
    const passwordInputs = ['currentPassword', 'newPassword', 'confirmPassword'];
    
    passwordInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        const toggleButton = document.querySelector(`.toggle-password[data-target="${inputId}"]`);
        
        // Show/hide eye icon based on input content
        input.addEventListener('input', function() {
            handlePasswordInput(this);
        });
        
        // Toggle password visibility when eye icon is clicked
        toggleButton.addEventListener('click', function() {
            togglePasswordVisibility(this);
        });
        
        // Also handle on focus to ensure icon appears if there's text
        input.addEventListener('focus', function() {
            if (this.value.length > 0) {
                toggleButton.style.display = 'block';
            }
        });
        
        // Hide icon when input loses focus and is empty
        input.addEventListener('blur', function() {
            if (this.value.length === 0) {
                toggleButton.style.display = 'none';
            }
        });
    });

    // Password change handler
    document.getElementById('savePasswordBtn').addEventListener('click', () => {
        const currentPassword = document.getElementById('currentPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();

        if (!currentPassword || !newPassword || !confirmPassword) {
            Swal.fire('Error', 'Please fill in all fields.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            Swal.fire('Error', 'New password and confirmation do not match.', 'error');
            return;
        }

        if (newPassword.length < 6) {
            Swal.fire('Error', 'New password must be at least 6 characters long.', 'error');
            return;
        }

        // Show loading state
        const saveBtn = document.getElementById('savePasswordBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Changing...';
        saveBtn.disabled = true;

        fetch('change_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    document.getElementById('changePasswordForm').reset();
                    // Hide all eye icons after successful password change
                    document.querySelectorAll('.toggle-password').forEach(btn => {
                        btn.style.display = 'none';
                    });
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    });

    // Reset password fields when modal is closed
    document.getElementById('changePasswordModal').addEventListener('hidden.bs.modal', function() {
        // Reset all password fields to hidden
        document.querySelectorAll('input[type="text"][id*="Password"]').forEach(input => {
            input.type = 'password';
        });
        // Reset all eye icons to default state and hide them
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.style.display = 'none';
            const icon = button.querySelector('i');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        });
        // Reset the form
        document.getElementById('changePasswordForm').reset();
    });

    // Also handle when modal is shown to ensure clean state
    document.getElementById('changePasswordModal').addEventListener('show.bs.modal', function() {
        // Ensure all eye icons are hidden initially
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.style.display = 'none';
        });
    });
});
</script> 