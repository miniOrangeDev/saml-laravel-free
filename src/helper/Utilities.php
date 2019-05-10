<?php

    namespace MiniOrange\Helper;

    use Exception;
    use MiniOrange\Helper\Exception\NotRegisteredException;

    class Utilities
    {
        /**
         * Get resource director path
         * @return string
         */
        public static function getResourceDir()
        {
            return MSSP_DIR.DIRECTORY_SEPARATOR.Constants::RESOURCE_FOLDER;
        }


        /**
         * This function checks if a value is set or
         * empty. Returns true if value is empty
         *
         * @return True or False
         * @param $value - references the variable passed.
         */
        public static function isBlank( $value )
        {
            if( ! isset( $value ) || empty( $value ) ) return TRUE;
            return FALSE;
        }


        /**
         * Get the Private Key File Path
         * @return string
         */
        public static function getPrivateKey()
        {
            return self::getResourceDir().DIRECTORY_SEPARATOR.Constants::SP_KEY;
        }

        public static function getAlternatePrivateKey(){
            return self::getResourceDir().DIRECTORY_SEPARATOR.Constants::SP_ALTERNATE_KEY;
        }

        /**
         * Get the Public Key File Path
         * @return string
         */
        public static function getPublicKey()
        {
            return self::getResourceDir().DIRECTORY_SEPARATOR.Constants::SP_KEY;
        }


        /**
         * Get Image Resource URL
         */
        public static function getImageUrl($imgFileName)
        {
            echo self::getBaseUrl().'/'.MSSP_NAME.'/resources/images/'.$imgFileName;exit;
            return self::getBaseUrl().'/'.MSSP_NAME.'/resources/images/'.$imgFileName;
        }


        /**
         * Get the base url of the site.
         * @return string
         */
        public static function getBaseUrl()
        {
            $pageURL = 'http';

            if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
                $pageURL .= "s";

            $pageURL .= "://";

            if ($_SERVER["SERVER_PORT"] != "80")
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
            else
                $pageURL .= $_SERVER["SERVER_NAME"];
            return $pageURL;
        }


        /**
         * The function returns the current page URL.
         * @return string
         */
        public static function currentPageUrl()
        {
            return self::getBaseUrl() . $_SERVER["REQUEST_URI"];
        }

        public static function check_certificate_format($certificate){
            if(!@openssl_x509_read($certificate)){
                throw new Exception("Certificate configured in the connector is in wrong format");
            }
        }

        /**
         * This function sanitizes the certificate
         */
        public static function sanitize_certificate( $certificate ) {
            $certificate = trim($certificate);
            $certificate = preg_replace("/[\r\n]+/", "", $certificate);
            $certificate = str_replace( "-", "", $certificate );
            $certificate = str_replace( "BEGIN CERTIFICATE", "", $certificate );
            $certificate = str_replace( "END CERTIFICATE", "", $certificate );
            $certificate = str_replace( " ", "", $certificate );
            $certificate = chunk_split($certificate, 64, "\r\n");
            $certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
            return $certificate;
        }

        public static function desanitize_certificate( $certificate ) {
            $certificate = preg_replace("/[\r\n]+/", "", $certificate);
            //$certificate = str_replace( "-", "", $certificate );
            $certificate = str_replace( "-----BEGIN CERTIFICATE-----", "", $certificate );
            $certificate = str_replace( "-----END CERTIFICATE-----", "", $certificate );
            $certificate = str_replace( " ", "", $certificate );
            //$certificate = chunk_split($certificate, 64, "\r\n");
            //$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
            return $certificate;
        }

        /**
         * Checks if the SAML Plugin has been configured or not. Checks if the
         * IDP cert, Login Url , entity id and SP ACS url and entity id has been
         * set.
         * @return bool
         */
        public static function isSPConfigured()
        {   
            $pluginSettings = PluginSettings::getPluginSettings();
            $certSet = !self::isBlank($pluginSettings->getX509Certificate())
            && strpos($pluginSettings->getX509Certificate(),"<") === false;
            $samlLoginUrlSet = !self::isBlank($pluginSettings->getSamlLoginUrl())
                && strpos($pluginSettings->getSamlLoginUrl(),"<") === false;
            $acsUrlSet = !self::isBlank($pluginSettings->getAcsUrl())
                && strpos($pluginSettings->getAcsUrl(),"<") === false;
            $entityIDSet = !self::isBlank($pluginSettings->getSpEntityId())
                && strpos($pluginSettings->getSpEntityId(),"<") === false;
            $idpEntityIDSet = !self::isBlank($pluginSettings->getIdpEntityId())
                && strpos($pluginSettings->getIdpEntityId(),"<") === false;

            return $certSet && $samlLoginUrlSet && $acsUrlSet && $entityIDSet && $idpEntityIDSet;
        }

        /**
         * Exception Page HTML Content
         * @param $message
         */
        public static function showErrorMessage($message)
        {
            echo '
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>We\'ve got some trouble | 500 - Webservice currently unavailable</title>
                    <style type="text/css">html{font-family:sans-serif;line-height:1.15;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,footer,header,nav,section{display:block}h1{font-size:2em;margin:.67em 0}figcaption,figure,main{display:block}figure{margin:1em 40px}hr{box-sizing:content-box;height:0;overflow:visible}pre{font-family:monospace,monospace;font-size:1em}a{background-color:transparent;-webkit-text-decoration-skip:objects}a:active,a:hover{outline-width:0}abbr[title]{border-bottom:none;text-decoration:underline;text-decoration:underline dotted}b,strong{font-weight:inherit}b,strong{font-weight:bolder}code,kbd,samp{font-family:monospace,monospace;font-size:1em}dfn{font-style:italic}mark{background-color:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}audio,video{display:inline-block}audio:not([controls]){display:none;height:0}img{border-style:none}svg:not(:root){overflow:hidden}button,input,optgroup,select,textarea{font-family:sans-serif;font-size:100%;line-height:1.15;margin:0}button,input{overflow:visible}button,select{text-transform:none}[type=reset],[type=submit],button,html [type=button]{-webkit-appearance:button}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button::-moz-focus-inner{border-style:none;padding:0}[type=button]:-moz-focusring,[type=reset]:-moz-focusring,[type=submit]:-moz-focusring,button:-moz-focusring{outline:1px dotted ButtonText}fieldset{border:1px solid silver;margin:0 2px;padding:.35em .625em .75em}legend{box-sizing:border-box;color:inherit;display:table;max-width:100%;padding:0;white-space:normal}progress{display:inline-block;vertical-align:baseline}textarea{overflow:auto}[type=checkbox],[type=radio]{box-sizing:border-box;padding:0}[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}[type=search]::-webkit-search-cancel-button,[type=search]::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}details,menu{display:block}summary{display:list-item}canvas{display:inline-block}template{display:none}[hidden]{display:none}/*! Simple HttpErrorPages | MIT X11 License | https://github.com/AndiDittrich/HttpErrorPages */body,html{width:100%;height:100%;background-color:#21232a}body{color:#fff;text-align:center;text-shadow:0 2px 4px rgba(0,0,0,.5);padding:0;min-height:100%;-webkit-box-shadow:inset 0 0 75pt rgba(0,0,0,.8);box-shadow:inset 0 0 75pt rgba(0,0,0,.8);display:table;font-family:"Open Sans",Arial,sans-serif}h1{font-family:inherit;font-weight:500;line-height:1.1;color:inherit;font-size:36px}h1 small{font-size:68%;font-weight:400;line-height:1;color:#777}a{text-decoration:none;color:#fff;font-size:inherit;border-bottom:dotted 1px #707070}.lead{color:silver;font-size:21px;line-height:1.4}.cover{display:table-cell;vertical-align:middle;padding:0 20px}footer{position:fixed;width:100%;height:40px;left:0;bottom:0;color:#a0a0a0;font-size:14px}</style>
                </head>
                <body>
                    <div class="cover">
                        <h1>Oops!! Something went wrong.</h1>
                        <p class="lead"> An unexpected condition was encountered.<br> </p>
                        <p>
                            '.$message.'
                        </p>
                    </div>        
                </body>
            ';
            exit;
        }
    }