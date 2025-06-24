<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
let usernameInput;
let passwordInput;

Swal.fire({
  title: 'Sign In',
  html: `
    <input type="text" id="username" class="swal2-input" placeholder="EmpID">
    <input type="password" id="password" class="swal2-input" placeholder="Password">
  `,
  confirmButtonText: 'Sign in',
  focusConfirm: false,
  didOpen: () => {
    const popup = Swal.getPopup();
    usernameInput = popup.querySelector('#username');
    passwordInput = popup.querySelector('#password');
    usernameInput.onkeyup = (event) => event.key === 'Enter' && Swal.clickConfirm();
    passwordInput.onkeyup = (event) => event.key === 'Enter' && Swal.clickConfirm();
  },
  preConfirm: () => {
    const username = usernameInput.value;
    const password = passwordInput.value;
    if (!username || !password) {
      Swal.showValidationMessage('Please enter username and password');
      return false;
    }
    return { username, password };
  }
}).then((result) => {
  if (result.isConfirmed && result.value) {
    const formData = new FormData();
    formData.append('empid', result.value.username);
    formData.append('password', result.value.password);

    fetch('login.php', {
      method: 'POST',
      body: formData
    })
    .then((res) => res.text())
    .then((response) => {
      if (response.trim() === 'success') {
        Swal.fire('Login Success!', 'Welcome to the system.', 'success').then(() => {
        //   window.location.href = 'dashboard.php';
        });
      } else {
        Swal.fire('Login Failed', response, 'error');
      }
    })
    .catch(() => {
      Swal.fire('Error', 'Could not contact the server.', 'error');
    });
  }
});
</script>

</body>
</html>
