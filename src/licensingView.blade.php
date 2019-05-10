<?php use MiniOrange\Helper\DB; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Main CSS-->
	<link rel="stylesheet" type="text/css" href="miniorange/sso/includes/css/main.css">
	<!-- Font-icon css-->
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="app sidebar-mini rtl">
<!-- Navbar-->
<header class="app-header"><a class="app-header__logo" href="#" style="margin-top:10px;"><img src="miniorange/sso/resources/images/logo-home.png"></a>
	<!-- Sidebar toggle button<a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a> -->
	<ul class="app-nav">
		<li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"><span  style="margin-left:5px"><?php
						echo DB::get_registered_user()->email;
						?>
</span><span style="padding-left:5px;"><i class="fa fa-caret-down"></i></span></i></a>
			<ul class="dropdown-menu settings-menu dropdown-menu-right">
				<li><a class="dropdown-item" href="admin_logout.php"><i class="fa fa-sign-out fa-lg"></i> Logout</a></li>
			</ul>
		</li>
	</ul>
</header>
<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>

<aside class="app-sidebar">
	<div class="app-sidebar__user" style="padding-left:40px"><img src="miniorange/sso/resources/images/miniorange.png"  style="width:37.25px; height:50px;" alt="User Image">
		<div style="margin-left:15px;">
			<p class="app-sidebar__user-name">PHP SAML</p>
			<p class="app-sidebar__user-designation">Connector</p>
		</div>
	</div>
	<ul class="app-menu">
		<li><a class="app-menu__item" href="setup.php"><i style="font-size:20px;" class="app-menu__icon fa fa-gear"></i><span class="app-menu__label"><b>Plugin Settings</b></span></a></li>
		<li><a class="app-menu__item" href="how_to_setup.php"><i style="font-size:20px;" class="app-menu__icon fa fa-info-circle"></i><span class="app-menu__label"><b>How to Setup?</b></span></a></li>
		<li><a class="app-menu__item active" href="licensing.php"><i style="font-size:20px;" class="app-menu__icon fa fa-dollar"></i><span class="app-menu__label"><b>Licensing</b></span></a></li>
		<li><a class="app-menu__item" href="support.php"><i style="font-size:20px;" class="app-menu__icon fa fa-support"></i><span class="app-menu__label"><b>Support</b></span></a></li>
	</ul>
</aside>

<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="fa fa-dollar"></i> Licensing</h1>

		</div>
		<ul class="app-breadcrumb breadcrumb">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item"><a href="#">Licensing</a></li>
		</ul>
	</div>

	<p id="saml_message"></p>
	<div class="row">
		<div class="col-md-12">
			<div class="tile">
				<div class="row">
					<div class="col-lg-10">
						<input type="hidden" value="" id="mo_customer_registered">
						<h5>You are using Free version of PHP SAML Connector.</h5>
						<br/>
						<div id="pricing_container" style="text-align:center; padding-bottom:30px;" >
							<h3 style="margin-left:180px"><b>Licensing Plans:</b></h3>
							<br/>
							<div style="display: -webkit-inline-box; display: -moz-inline-box; margin-left:300px; width:300px!important;">
								<div class="thumbnail" style="margin-left:-320px; margin-right:10px;">
									<div class="mo-tab" style="margin-left:50px">
										<h3>Free</h3>
										<br/><br/>
										<!-- <a class="btn btn-primary btn-large" href="downloads/php-saml-single-sign-on-trial.zip" target="_blank">Download</a> -->
										<hr>

										<h4 style="padding-top:20px;margin-bottom:45px;">$0</h4>
										<hr>
										<p>Unlimited Authentications</p>
										<p>Configurable SP Base URL</p>
										<p>Custom Application URL</p>
										<p>SSO button on Login page</p>
										<p>Standard Attribute Mapping(Only NameID)</p>
										<p>&nbsp</p>
										<p>&nbsp</p>
										<p>&nbsp</p>
										<p>&nbsp</p>


										<hr>
										<h4>Basic Email Support</h4>
										<br/>
									</div>
								</div>
								<div class="thumbnail" style="margin-right:10px">
									<div class="mo-tab">
										<h3>Premium</h3>
										<a class="btn btn-large" id="upgrade_button" style="background-color:#f7934c;color:#fff" href="#customer"  >Upgrade</a>
										<hr>
										<script>

											let anchorlinks = document.querySelectorAll('a[href^="#"]')

											for (let item of anchorlinks) { // relitere
												item.addEventListener('click', (e)=> {
													let hashval = item.getAttribute('href')
													let target = document.querySelector(hashval)
													target.scrollIntoView({
														behavior: 'smooth',
														block: 'start'
													})
													history.pushState(null, null, hashval)
													e.preventDefault()
												})
											}
										</script>
										<h4 style="padding-top:5px;">$250<br/>+<br/>Integration cost(optional)</h4>
										<hr>
										<p>Unlimited Authentications</p>
										<p>Configurable SP Base URL</p>
										<p>Custom Application URL and Site Logout URL</p>
										<p>SSO button on Login page</p>
										<p>Custom Attribute mapping</p>
										<p>Configurable SAML request binding type</p>
										<p>SAML Single Logout</p>
										<p>Force Authentication and Auto-Redirect</p>
										<p>Signed Response and Assertion</p>
										<hr>
										<h4>Premium Support</h4>
										<br/>
									</div>
								</div>
							</div>
						</div>
						<div class="tile">
							<div class="row">
								<div class="col-lg-12">
									<div id="customer">
										<?php
										if (mo_saml_is_customer_registered()) {
											mo_saml_show_customer_details();
										}
										if (DB::get_option("mo_saml_verify_customer")==true) {
											DB::delete_option("mo_saml_new_registration");
											mo_saml_show_verify_password_page();
										}
										if(DB::get_option("mo_saml_new_registration")==true) {
											mo_saml_show_registration_page();
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
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
    message.innerText = "' . setupDB::get_option('mo_saml_message') . '"
    </script>';
	unset($_SESSION['show_error_msg']);
	exit();
}
?>