
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Main CSS-->
<link rel="stylesheet" type="text/css"
	href="miniorange/sso/includes/css/main.css">
<!-- Font-icon css-->
<link rel="stylesheet" type="text/css"
	href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Register - miniOrange Admin</title>
</head>
<body>
	<section class="material-half-bg">
		<div class="cover"></div>
	</section>
	<section class="login-content">
		<div class="logo">
			<h1>
				<img src="miniorange/sso/resources/images/logo_large.png">
			</h1>
		</div>
		<div class="col-md-6">
			<div class="tile">
				<h3 class="tile-title"
					title="This will restrict unauthorized entity from accessing the Connector">Create
					a local account</h3>
				<form class="register_form" id="register_form" method="POST"
					action="register.php">
					<input type="hidden" name="option" value="register">
					<div class="tile-body">

						<div class="form-group row">
							<label class="control-label col-md-3">Email</label>
							<div class="col-md-8">
								<input class="form-control col-md-10" type="email" name="email"
									placeholder="Enter email address" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-md-3">Password</label>
							<div class="col-md-8">
								<input class="form-control col-md-10" type="password"
									id="password" name="password" placeholder="Enter a password"
									minlength="6" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-md-3">Confirm Password</label>
							<div class="col-md-8">
								<input class="form-control col-md-10" type="password"
									id="confirm_password" placeholder="Re-type the password"
									minlength="6" required>
							</div>
						</div>
						<script>
                var password = document.getElementById("password")
                , confirm_password = document.getElementById("confirm_password");

                function validatePassword(){
                  if(password.value != confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords Don't Match");
                  } else {
                    confirm_password.setCustomValidity('');
                  }
                }

                password.onchange = validatePassword;
                confirm_password.onkeyup = validatePassword;
                </script>

					</div>
					<div class="tile-footer">
						<div class="row">
							<div class="col-md-8 col-md-offset-3">
								<button class="btn btn-primary" type="submit" id="register">
									<i class="fa fa-fw fa-lg fa-check-circle"></i>Register
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

	</section>
	<!-- Essential javascripts for application to work-->
	<script src="miniorange/sso/includes/js/jquery-3.2.1.min.js"></script>
	<script src="miniorange/sso/includes/js/popper.min.js"></script>
	<script src="miniorange/sso/includes/js/bootstrap.min.js"></script>
	<script src="miniorange/sso/includes/js/main.js"></script>
	<!-- The javascript plugin to display page loading on top-->
	<script src="miniorange/sso/includes/js/plugins/pace.min.js"></script>
</body>
</html>
<?php
use MiniOrange\Helper\DB as setupDB;
if (isset($_SESSION['show_success_msg'])) {

    echo '<script>
    var message = document.getElementById("saml_message");
    message.classList.add("success-message");
    message.innerText = "' . setupDB::get_option('mo_saml_message') . '"
    </script>';
    unset($_SESSION['show_success_msg']);
    exit();
}
if (isset($_SESSION['show_error_msg'])) {
    echo '<script>
    var message = document.getElementById("saml_message");
    message.classList.add("error-message");
    message.innerText = "' . DB::get_option('mo_saml_message') . '"
    </script>';
    unset($_SESSION['show_error_msg']);
}
?>