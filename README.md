# What is Single Sign-On (SSO)?
**Single Sign-On** is an authorization and authentication process that enables an user to connect to multiple enterprise applications using a single set of credentials. Simply put, SSO combines multiple application login pages into just one, allowing you to submit credentials just once and gain access to all the applications without having to log in to each one individually. End users save time and effort by not having to sign into and out of a variety of on-premises, web and cloud applications on a regular basis.

SSO or single sign-on is a critical component of the Identity and Access Management or access privileges services. SSO solution perfectly implemented within an enterprise simplifies overall password management, improving productivity and security, lowering the likelihood of weak, lost, or forgotten passwords

# Laravel SAML SSO 
Laravel package for SAML Single Sign On (SSO). 
The package acts as a SAML Service Provider (SP). 
SAML Single Sign On (SSO) for Laravel allows users sign in to Laravel webapp with your SAML 2.0 compatible Identity Provider. 
We support all known IdPs - Google Apps, ADFS, Okta, miniOrange, OneLogin, Azure AD, Salesforce, Shibboleth, SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, Bitium, WSO2, NetIQ etc. SAML Laravel application acts as a SAML 2.0 Service Provider (SP) and securely authenticate users with your SAML 2.0 Identity Provider.

## Requirements
* Laravel - 5.0+
* PHP - ^5.1 || ^7.1 || ^8.0

## Installation - Composer
1. Install the package via composer in your Laravel app's main directory.
    ```
composer require miniorange/saml-laravel-free
```

> Note: If you are using Laravel 5.4 or below, you will need to add the following value to the **'providers'** array in your **app.php** file which can be found in the **project\config** folder.
>````
'providers' => [
    ...
    provider\ssoServiceProvider::class,
    ...
]

2. After successful installation of package, go to your Laravel app in the browser and enter

   ***{laravel-application-domain}/mo_admin***

3. The package will start setting up your database for you and then redirect you to the admin registration page where you can register or login with miniOrange and setup your Identity Provider.

    ![This is plugin login page](https://plugins.miniorange.com/wp-content/uploads/2020/11/plugin-settings.webp)
    
## Configuring the package

1. You can configure the SP Base URL or leave this option as it is.
Also, you need to provide these SP Entity ID and ACS URL values while configuring your Identity Provider.
    ![This is plugin login page](https://plugins.miniorange.com/wp-content/uploads/2022/11/maual-sp-metadata.webp)
    
2. Use your Identity Provider details to configure the plugin as by uploading IDP metadata file/XML provided by your Identity Provider or entering the details manually.
    
    ![This is plugin setting page](https://plugins.miniorange.com/wp-content/uploads/2022/11/download-the-sp-metadata.webp)
    
    Or
    
    ![This is plugin setting page](https://plugins.miniorange.com/wp-content/uploads/2022/11/manually-add-idp-details.webp)

3. Click on Save button.
    
## Test Configuration
1. You can test if the package is configured properly or not by clicking on the Test Configuration button. You should see a Test Successful screen as shown below along with the user's attribute values.
    ![This is plugin setting page](https://plugins.miniorange.com/wp-content/uploads/2022/11/test-confi.webp)

    ![This is plugin test configuration page](https://plugins.miniorange.com/wp-content/uploads/2020/11/laravel-sso-test-result.webp)
    
## Adding Single Sign On button on the application login page (Optional)

Once the package is installed, you can add a **Single Sign On** button in your application login page using these commands in order:

1. Install the Laravel UI Package.
````
composer require laravel/ui
````
2. Generate Auth Routes using VueJs
````
php artisan ui vue --auth
````
3. Install Node modules and run the development
````
npm install && npm run dev
````
4. Migrate and update the database
````
php artisan migrate 
````
The Laravel application login page should look something like this then.

    ![This is laravel login page](https://plugins.miniorange.com/wp-content/uploads/2020/11/laravel-sso-button.webp)

# Features
The features provided in the free and premium are listed here.

| Free Plan                       | Premium Plan                                        |
| :-----------------------------: |:---------------------------------------------------:|
| Simple and easy-to-use admin UI | Simple and easy-to-use admin UI                     |
| SSO upto 10 users               | SSO for unlimited users                             |
| Unlimited Authentications       | Unlimited Authentications                           |
| Auto-create users in Laravel    | Auto-create users in Laravel                        |
| SSO button on Login page        | SSO button on Login page                            |
| Relay State URL                 | Relay state URL                                     |
| Configurable SP Base URL        | Configurable SP Base URL                            |
|                                 | Custom Attribute mapping                            |
|                                 | Signed and Encrypted Request Support                |
|                                 | Signed and Encrypted Assertion and Response Support |
|                                 | Configurable SAML request binding type              |
|                                 | Protect Complete Site and Auto-Redirect             |

# Feature Description

* **Custom Attribute Mapping**

    It allows you to map the received custom attributes sent by your Identity Provider (IdP) to the Service Provider _(Laravel Application)_.
* **Auto-create users in Laravel**

    Creates the users from the IdP to SP (Laravel Application) when SSO is done.
* **Signed and Encrypted Assertion and Response Support**

    To verify the authenticity of the source of SAML Assertion and Response thereby improving the security.
* **Protect Complete Site and Auto-Redirect**

    Asking user to login via SSO if the user session does not exist everytime the site is accessed.

# Single Sign On (SSO)

The Single Sign On can be initiated using ***{laravel-application-domain}/sso.php*** or the Single Sign On button (if added using the commands above) on the login page of the Laravel application.