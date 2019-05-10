<?php use MiniOrange\Helper\DB;?>
<!DOCTYPE html>
<html lang="en">
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
</head>
<body class="app sidebar-mini rtl">
	<!-- Navbar-->
	<header class="app-header">
		<a class="app-header__logo" href="#" style="margin-top: 10px;"><img
			src="miniorange/sso/resources/images/logo-home.png"></a>
		<!-- Sidebar toggle button<a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a> -->
		<ul class="app-nav">
			<li class="dropdown"><a class="app-nav__item" href="#"
				data-toggle="dropdown" aria-label="Open Profile Menu"><i
					class="fa fa-user fa-lg"><span style="margin-left: 5px"><?php  DB::get_registered_user()->email; ?></span><span
						style="padding-left: 5px;"><i class="fa fa-caret-down"></i></span></i></a>
				<ul class="dropdown-menu settings-menu dropdown-menu-right">
					<li><a class="dropdown-item" href="admin_logout.php"><i
							class="fa fa-sign-out fa-lg"></i> Logout</a></li>
				</ul></li>
		</ul>
	</header>
	<!-- Sidebar menu-->
	<div class="app-sidebar__overlay" data-toggle="sidebar"></div>

	<aside class="app-sidebar">
		<div class="app-sidebar__user" style="padding-left: 9%">
			<img src="miniorange/sso/resources/images/miniorange.png"
				style="width: 37.25px; height: 50px;" alt="User Image">
			<div style="margin-left: 15px;">
				<p class="app-sidebar__user-name">Laravel SSO SP</p>
				<p class="app-sidebar__user-designation">Plugin</p>
			</div>
		</div>
		<ul class="app-menu">
			<li><a class="app-menu__item" href="setup.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-gear"></i><span
					class="app-menu__label"><b>Plugin Settings</b></span></a></li>
			<li><a class="app-menu__item" href="how_to_setup.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-info-circle"></i><span
					class="app-menu__label"><b>How to Setup?</b></span></a></li>
			<li><a class="app-menu__item" href="licensing.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-dollar"></i><span
					class="app-menu__label"><b>Licensing</b></span></a></li>			<li><a class="app-menu__item active" href="support.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-support"></i><span
					class="app-menu__label"><b>Support</b></span></a></li>
		</ul>
	</aside>

	<main class="app-content">
	<div class="app-title">
		<div>
			<h1>
				<i class="fa fa-support"></i> Support/Contact Us
			</h1>

		</div>
		<ul class="app-breadcrumb breadcrumb">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item"><a href="#">Support/Contact Us</a></li>
		</ul>
	</div>

	<p id="saml_message"></p>

	<div class="row">
		<div class="col-md-12">
			<div class="tile">
				<div class="row">
					<div class="col-lg-10">
						<form method="post" action="">
							<p>
								<b>Need any help? We can help you in configuring the connector
									with your Identity Provider. Just send us a query and we will
									get back to you soon.</b>
							</p>
							<input type="hidden" name="option"
								value="mo_saml_contact_us_query_option" />
							<div class="form-group">
								<input class="form-control" type="email"
									name="mo_saml_contact_us_email" placeholder="Enter your email"
									required
									value="">
							</div>
							<div class="form-group">
								<input class="form-control" type="tel"
									name="mo_saml_contact_us_phone" required
									pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}"
									placeholder="We call only if you do. 	( eg.+1 9876543210, +91 1234567890 )">
							</div>
							<div class="form-group">
								<textarea class="form-control" name="mo_saml_contact_us_query"
									required placeholder="Enter your query here"
									onkeypress="mo_saml_valid_query(this)"
									onkeyup="mo_saml_valid_query(this)"
									onblur="mo_saml_valid_query(this)"></textarea>
							</div>
					
					</div>
				</div>
				<div class="tile-footer">
					<button class="btn btn-primary" type="submit" name="submit">Submit</button>
				</div>
				</form>
			</div>
		</div>
	</div>
	</main>

	<script>
        function mo_saml_valid_query(f) {
            !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
                /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
        }
    </script>
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