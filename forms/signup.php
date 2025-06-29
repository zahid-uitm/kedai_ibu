<!DOCTYPE html>
<html>
<head>
  <title>Sign Up</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
let empidInput, passwordInput

Swal.fire({
  title: 'Sign Up',
  html: `
    <input type="text" id="empid" class="swal2-input" placeholder="Employee ID">
    <input type="password" id="password" class="swal2-input" placeholder="Create Password">
  `,
  confirmButtonText: 'Sign Up',
  focusConfirm: false,
  didOpen: () => {
    const popup = Swal.getPopup();
    empidInput = popup.querySelector('#empid');
    passwordInput = popup.querySelector('#password');
    empidInput.onkeyup = (e) => e.key === 'Enter' && Swal.clickConfirm();
    passwordInput.onkeyup = (e) => e.key === 'Enter' && Swal.clickConfirm();
  },
  preConfirm: () => {
    const empid = empidInput.value.trim();
    const password = passwordInput.value;
    if (!empid || !password) {
      Swal.showValidationMessage('Employee ID and Password are required');
      return false;
    }
    return { empid, password };
  }
}).then((result) => {
  if (result.isConfirmed && result.value) {
    const formData = new FormData();
    formData.append('empid', result.value.empid);
    formData.append('password', result.value.password);

    fetch('signup.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(response => {
      if (response.trim() === 'success') {
        Swal.fire('Sign Up Complete!', 'You may now login.', 'success').then(() => {
        //   window.location.href = 'login.html';
        });
      } else {
        Swal.fire('Sign Up Failed', response, 'error');
      }
    })
    .catch(() => {
      Swal.fire('Error', 'Could not connect to server.', 'error');
    });
  }
});
</script>

</body>
</html>
