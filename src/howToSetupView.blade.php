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
		<!-- Sidebar toggle button -->
		<ul class="app-nav">
			<li class="dropdown"><a class="app-nav__item" href="#"
				data-toggle="dropdown" aria-label="Open Profile Menu"><i
					class="fa fa-user fa-lg"><span style="margin-left: 5px"><?php echo DB::get_registered_user()->email; ?></span><span
						style="padding-left: 5px;"><i class="fa fa-caret-down"></i></span></i></a>
				<ul class="dropdown-menu settings-menu dropdown-menu-right">
					<li><a class="dropdown-item" href="admin_logout.php"><i
							class="fa fa-sign-out fa-lg"></i>Logout</a></li>
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
			<li><a class="app-menu__item active" href="how_to_setup.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-info-circle"></i><span
					class="app-menu__label"><b>How to Setup?</b></span></a></li>
			<li><a class="app-menu__item" href="licensing.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-dollar"></i><span
					class="app-menu__label"><b>Licensing</b></span></a></li>
			<li><a class="app-menu__item" href="support.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-support"></i><span
					class="app-menu__label"><b>Support</b></span></a></li>
		</ul>
	</aside>
	<main class="app-content">
	<div class="app-title">
		<div>
			<h1>
				<i class="fa fa-info-circle"></i> How to Setup?
			</h1>

		</div>
		<ul class="app-breadcrumb breadcrumb">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item"><a href="#">How to Setup?</a></li>
		</ul>
	</div>
	<p id="saml_message"></p>
	<div class="row">
		<div class="col-md-12">
			<div class="tile">
				<div class="row">
					<div class="col-lg-10">
						<h3>Follow these steps to setup the plugin:</h3>
						<h4>Step 1:</h4>
						<p>
							In <b>Plugin Settings</b>, use your Identity Provider details to
							configure the plugin
						</p>
						<img src="miniorange/sso/resources/images/setup_1.png"
							style="width: 800px; height: 380px; margin-left: 50px; border: 1px solid;">
						<br /> <br />
						<h4>Step 2:</h4>
						<ul>
							<li>Configure the plugin using the Service Provider details.</li>
							<li>You need to provide these <b>SP Entity ID</b>, <b>ACS URL</b>
								and <b>Single Logout URL</b> values while configuring your
								Identity Provider
							</li>
							<li>Click on the <b>Save</b> button to save your configuration.
							</li>
						</ul>
						<img src="miniorange/sso/resources/images/setup_2.png"
							style="width: 800px; height: 380px; margin-left: 50px; border: 1px solid;">
						<br /> <br />
						<h4>Step 3:</h4>
						<ul>
							<li>You can test if the plugin configured properly or not by
								clicking on the <b>Test Configuration</b> button.
							</li>
						</ul>
						<img src="miniorange/sso/resources/images/setup_3.png"
							style="width: 800px; height: 380px; margin-left: 50px; border: 1px solid;">
						<ul>
							<br />
							<li>If the configuration is correct, you should see a Test
								Successful screen with the user's attribute values.</li>
						</ul>
						<img src="miniorange/sso/resources/images/setup_4.png"
							style="width: 600px; height: 400px; margin-left: 50px; border: 1px solid;">
						<br /> <br />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	</main>

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