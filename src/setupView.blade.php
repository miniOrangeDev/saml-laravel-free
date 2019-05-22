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
<link
	href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css"
	rel="stylesheet" id="bootstrap-css">
<script
	src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
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
					class="fa fa-user fa-lg"><span style="margin-left: 5px"><?php echo DB::get_registered_user()->email; ?></span><span
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
			<li><a class="app-menu__item active" href="setup.php"><i
					style="font-size: 20px;" class="app-menu__icon fa fa-gear"></i><span
					class="app-menu__label"><b>Plugin Settings</b></span></a></li>
			<li><a class="app-menu__item" href="how_to_setup.php"><i
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
				<i class="fa fa-gear"></i> Plugin Settings
			</h1>

		</div>
		<ul class="app-breadcrumb breadcrumb">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item"><a href="#">Plugin Settings</a></li>
		</ul>
	</div>
	<p id="saml_message"></p>
	<div class="row">
		<div class="col-md-12">
			<div class="tile">
                <div class="row">
						<div class="col-lg-6">
							<form method="POST" action="" id="saml_form">
								<input type="hidden" name="option"
									value="save_connector_settings">
								<h4>Identity Provider Settings</h4>
								<br>
								<div class="form-group">
									<label for="idp_name"><b>Identity Provider Name</b></label> <input
										class="form-control" name="idp_name" id="idp_name" type="text"
										required
										<?php
        echo ' value="' . DB::get_option('saml_identity_name') . '" ';
        ?>>
								</div>
								<div class="form-group">
									<label for="idp_entity_id"><b>IDP Entity ID</b></label> <input
										class="form-control" name="idp_entity_id" id="idp_entity_id"
										type="text" required
										<?php
        echo ' value="' . DB::get_option('idp_entity_id') . '" ';
        ?>>
								</div>
								<div class="form-group">
									<label for="saml_login_url"><b>SAML Login URL</b></label> <input
										class="form-control" name="saml_login_url" id="saml_login_url"
										type="URL" required 
										<?php
        echo ' value="' . DB::get_option('saml_login_url') . '" ';
        ?>>
								</div>

								<label><b>SAML Login Binding type</b></label>
								<table>
									<tr>
										<td style="padding: 10px;">
											<div class="animated-radio-button">
												<label> <input type="radio" name="login_binding_type"
													id="http_redirect_binding" value="HttpRedirect"
													<?php
													if (DB::get_option('saml_login_binding_type') === 'HttpRedirect' || DB::get_option('saml_login_binding_type') == '' ) {
                echo ' checked ';
            }
            ?>><span class="label-text">Http-Redirect</span>
												</label>
											</div>
										</td>
										<td style="padding: 10px;">

											<div class="animated-radio-button">
												<label> <input type="radio" name="login_binding_type"
													id="http_post_binding" value="HttpPost" disabled><span class="label-text">Http-Post</span>
													<h6 class="premium-indicator">Available in Premium Version</h6>
												</label>

											</div>

										</td>
									</tr>
								</table>


								<div class="form-group">
									<label for="saml_logout_url"><b>SAML Logout URL</b> <h6 class="premium-indicator">Available in Premium Version</h6> </label><input
										class="form-control" name="saml_logout_url"
										id="saml_logout_url" type="URL" disabled>

								</div>


								<div class="form-group">
									<label for="x509_certificate"><b>SAML x509 Certificate</b></label>
									<textarea class="form-control" id="x509_certificate"
										name="x509_certificate" rows="5" required><?php echo DB::get_option('saml_x509_certificate');?></textarea>
									<small><b>NOTE:</b> Format of the certificate:<br /> <b>-----BEGIN
											CERTIFICATE-----<br />XXXXXXXXXXXXXXXXXXXXXXXXXXX<br />-----END
											CERTIFICATE-----
									</b></small><br />
								</div>

								<div class="form-group">
									<label for="signing_info"><b>Signing</b></label>
									<p>
										Supports both Signed Assertion and Signed Response<i
											class="fa fa-question-circle" id="signing_info"
											style="padding-left: 5px;"></i><br />


									<div style="display: none;" class="help_desc"
										id="signing_help_desc">Please make sure that your Identity
										Provider sign atleast one of them.</div>
									</p>
								</div>
								<div class="animated-checkbox">
									<label> <input type="checkbox" id="force_authn"
										name="force_authn" disabled><span class="label-text">Force Authentication</span> <h6 class="premium-indicator">Available in Premium Version</h6>
									</label>&nbsp

								</div>
								<div class="animated-checkbox">
									<label> <input type="checkbox" id="force_sso" name="force_sso" disabled><span class="label-text">Force Single Sign On</span> <h6 class="premium-indicator">Available in Premium Version</h6>
									</label>&nbsp
									<input hidden="true" id="saml_submit"
										type="submit">
								</div>
								<div class="tile-footer">
									<button class="btn btn-primary" type="button"
											name="submit_saml_form" id="submit_saml_form"
											style="margin-right: 10px;"
											onClick="jQuery('#saml_submit').click();">Save</button>
									<a target="_blank" href="login.php?RelayState=testconfig"
									   style="text-decoration: none"><button class="btn btn-primary"
																			 name="do_sso" type="button">Test Configuration</button></a>
								</div>

						</div>
						<div class="col-lg-4 offset-lg-1">
							<h4>Service Provider Settings</h4>
							<br>
							<div class="form-group">
								<label for="site_base_url"><b>Base URL</b></label> <input
									class="form-control" id="site_base_url" name="site_base_url"
									type="text"
									<?php
        $base_url = '';
        if (DB::get_option('sp_base_url')) {
            $base_url = DB::get_option('sp_base_url');
        } else {
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $base_url = str_replace("setup.php", "", $actual_link);
        }
        echo ' value= "' . $base_url . '" ';

        ?>>
							</div>

							<div class="form-group">
								<label for="sp_entity_id"><b>SP Entity ID</b></label> <input
									class="form-control" id="sp_entity_id" name="sp_entity_id"
									type="text" readonly=""
									<?php
        $entity_id = $base_url . 'miniorange_php_saml_connector';
        echo ' value="' . $entity_id . '" ';
        ?>>
							</div>

							<div class="form-group">
								<label for="acs_url"><b>ACS URL</b></label> <input
									class="form-control" id="acs_url" name="acs_url" type="text"
									readonly=""
									<?php
        $acs = $base_url . 'sso.php';
        echo ' value="' . $acs . '" ';
        ?>>
							</div>

							<div class="form-group">
								<label for="slo_url"><b>SINGLE LOGOUT URL</b> <h6 class="premium-indicator">Available in Premium Version</h6></label> <input
									class="form-control" id="slo_url" name="slo_url" type="text"
									readonly=""
									<?php
        $slo = $base_url . 'logout.php';
        echo ' value="' . $slo . '" ';
        ?>>
							</div>
							<div class="form-group">
								<label><b>RelayState URL</b>
									<i class="fa fa-question-circle" id="relaystate_url_help"></i>
									<div style="display: none" class="help_desc"
										 id="relaystate_url_help_desc">
										<span>The users will be redirected to this URL after logging
											in.</span>
									</div> </label> <input class="form-control"
														   name="relaystate_url" id="relaystate_url" type="text"
								<?php
										echo ' value="' . DB::get_option('relaystate_url') . '" ';
										?>>
								<div class="form-group">
									<label><b>Site Logout URL</b> <i class="fa fa-question-circle" id="logout_url_help"></i>
										<h6 class="premium-indicator">Available in Premium Version</h6>
										<div style="display: none" class="help_desc"
											 id="logout_url_help_desc">The users will be redirected to this
											URL after logging out.</div> </label> <input disabled
											class="form-control" name="site_logout_url"
											id="site_logout_url" type="text"
									<?php
											echo ' value="' . DB::get_option('site_logout_url') . '" ';
											?>>
									<br>
							<div class="form-group">
								<label><b>SP Certificate:</b></label> <a
									style="padding-left: 5px;" href="#" id="download_sp_cert">Download</a><i
									class="fa fa-question-circle" id="sp_certificate_help"
									style="padding-left: 5px;"></i> <br />
								<div class="help_desc" id="sp_certificate_help_desc"
									style="display: none;">Provide this certificate to your
									Identity Provider for encryption or signing.</div>
							</div>


							</form>
							<fieldset disabled="disabled">
							<h4>Attribute Mapping</h4> <h6 class="premium-indicator">Available in Premium Version</h6>
							<br>

							<form id="attrs_form" method="post" action="">
								<input type="hidden" name="option" value="attribute_mapping">
								<div class="form-group">
									<label for="saml_am_email"><b>EMAIL</b></label> <input
										class="form-control" id="saml_am_email" name="saml_am_email"
										type="text"
										value="NameID">
								</div>



								<div class="form-group">
									<label for="saml_am_username"><b>NAME</b></label> <input
										class="form-control" id="saml_am_username"
										name="saml_am_username" type="text"
										value="NameID">
								</div>

								<h4>Custom Attribute Mapping</h4>
								<div data-role="dynamic-fields">
									<!-- /div.form-inline -->
                            <div class="form-inline">
										<div class="form-group">
											<input type="text" class="form-control" id="attribute_name"
												name="attribute_name[]" placeholder="eg. Username"> <span>&nbsp;</span>
											<input type="text" class="form-control" id="attribute_value"
												name="attribute_value[]" placeholder="eg. Attribute 1"> <span>&nbsp;</span>
											<button class="btn btn-danger" data-role="remove">
												<span class="glyphicon glyphicon-remove"></span>
											</button>
											<button class="btn btn-primary" data-role="add">
												<span class="glyphicon glyphicon-plus"></span>
											</button>
										</div>
									</div>
									<!-- /div.form-inline -->
                    </div>
								<!-- /div[data-role="dynamic-fileds"] -->
								<br />
								<button type="submit" class="btn btn-primary"
									name="custom_attrs" id="custom_attrs">Save Attribute Mapping</button>
								<!-- <button type="button" class="btn btn-primary">Add Attribute</button> -->

							</form>
							</fieldset>
						</div>

					</div>
					<hr>
					<br />

                      </fieldset>
                </div>
		</div>
	</div>
		</div></div>
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
    message.innerText = "' . setupDB::get_option('mo_saml_message') . '"
    </script>';
    unset($_SESSION['show_error_msg']);
    exit();
}
?>