<?php
namespace MiniOrange;

use Illuminate\Http\Request;
use MiniOrange\Classes\Actions\ProcessResponseAction;
use MiniOrange\Classes\Actions\ProcessUserAction;
use MiniOrange\Classes\Actions\ReadResponseAction;
use MiniOrange\Classes\Actions\TestResultActions;
use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Messages;
use MiniOrange\Helper\Utilities;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Classes\Actions\AuthFacadeController;
use MiniOrange\Helper\Lib\AESEncryption;
use Illuminate\Support\Facades\Session;

final class SSO
{

    public function __construct()
    {
        //exit("here2");
        $pluginSettings = PluginSettings::getPluginSettings();
        if (array_key_exists('SAMLResponse', $_REQUEST) && ! empty($_REQUEST['SAMLResponse'])) {
            try {
                //exit("here1");
                $relayStateUrl = array_key_exists('RelayState', $_REQUEST) ? $_REQUEST['RelayState'] : '/';
                $samlResponseObj = ReadResponseAction::execute(); // read the samlResponse from IDP
                $responseAction = new ProcessResponseAction($samlResponseObj);
                $responseAction->execute();
                $ssoemail = current(current($samlResponseObj->getAssertions())->getNameId());
                $attrs = current($samlResponseObj->getAssertions())->getAttributes();
                $attrs['NameID'] = array(
                    "0" => $ssoemail
                );
                $sessionIndex = current($samlResponseObj->getAssertions())->getSessionIndex();

                if (strcasecmp($relayStateUrl, Constants::TEST_RELAYSTATE) == 0) {
                    (new TestResultActions($attrs))->execute(); // show test results
                } else {
                    (new ProcessUserAction($attrs, $relayStateUrl, $sessionIndex))->execute(); // process user action

                    session_id('attributes');
                    session_start();
                    $_SESSION['email'] = $attrs['NameID'];
                    $_SESSION['username'] = $attrs['NameID'];
                    $encrypted_mail = urlencode(AESEncryption::encrypt_data($_SESSION['email'][0], "secret"));
                    $encrypted_name = urlencode(AESEncryption::encrypt_data($_SESSION['username'][0], "secret"));
                    header('Location: sign?email=' . $encrypted_mail . '&name=' . $encrypted_name);
                    exit();
                }
            } catch (\Exception $e) {
                if (strcasecmp($relayStateUrl, Constants::TEST_RELAYSTATE) === 0)
                    (new TestResultActions(array(), $e))->execute();
                else
                    Utilities::showErrorMessage($e->getMessage());
            }
        } else {
            Utilities::showErrorMessage(Messages::MISSING_SAML_RESPONSE);
        }
    }
}
new SSO();