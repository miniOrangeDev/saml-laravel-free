<?php

namespace MiniOrange\Classes\Actions;

use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Exception\SAMLResponseException;
use MiniOrange\Helper\SAMLUtilities;
use MiniOrange\Helper\Utilities;

/**
 * This action class shows the attributes coming in the SAML
 * response in a tabular form indicating if the Test SSO
 * connection was successful. Is used as a reference to do
 * attribute mapping.
 *
 * @todo - Move the html code to template files and pick it from there
 */
class TestResultActions
{
    private $attrs;
    private $samlException;
    private $hasExceptionOccurred;
    private $nameId;

    private $template = '<div style="font-family:Calibri;padding:0 3%%;">{{header}}{{commonbody}}{{footer}}</div>';
    private $successHeader  = ' <div style="color: #3c763d;background-color: #dff0d8; padding:2%%;margin-bottom:20px;text-align:center; 
                                    border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL
                                </div>
                                <div style="display:block;text-align:center;margin-bottom:4%%;"><img style="width:15%%;" src="{{right}}"></div>';

    private $errorHeader    = ' <div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;
                                    border:1px solid #E6B3B2;font-size:18pt;">TEST FAILED
                                </div><div style="display:block;text-align:center;margin-bottom:4%%;"><img style="width:15%%;"src="{{wrong}}"></div>';

    private $commonBody  = '<span style="font-size:14pt;"><b>Hello</b>, {{email}}</span><br/>
                                <p style="font-weight:bold;font-size:14pt;margin-left:1%%;">ATTRIBUTES RECEIVED:</p>
                                <table style="border-collapse:collapse;border-spacing:0; display:table;width:100%%; 
                                    font-size:14pt;background-color:#EDEDED;">
                                    <tr style="text-align:center;">
                                        <td style="font-weight:bold;border:2px solid #949090;padding:2%%;">ATTRIBUTE NAME</td>
                                        <td style="font-weight:bold;padding:2%%;border:2px solid #949090; word-wrap:break-word;">ATTRIBUTE VALUE</td>
                                    </tr>{{tablecontent}}
                                </table>';

    private $exceptionBody = '<div style="margin: 10px 0;padding: 12px;color: #D8000C;background-color: #FFBABA;font-size: 16px;
                                line-height: 1.618;overflow-wrap:break-word;">{{exceptionmessage}}</div>{{certErrorDiv}}{{samlResponseDiv}}';

    private $certError = '<p style="font-weight:bold;font-size:14pt;margin-left:1%%;">CERT CONFIGURED IN PLUGIN:</p><div style="color: #373B41;
                                font-family: Menlo,Monaco,Consolas,monospace;direction: ltr;text-align: left;white-space: pre;
                                word-spacing: normal;word-break: normal;font-size: 13px;font-style: normal;font-weight: 400;
                                height: auto;line-height: 19.5px;border: 1px solid #ddd;background: #fafafa;padding: 1em;
                                margin: .5em 0;border-radius: 4px;">{{certinplugin}}</div>
                            <p style="font-weight:bold;font-size:14pt;margin-left:1%%;">CERT FOUND IN RESPONSE:</p><div style="color: #373B41;
                                font-family: Menlo,Monaco,Consolas,monospace;direction: ltr;text-align: left;white-space: pre;
                                word-spacing: normal;word-break: normal;font-size: 13px;font-style: normal;font-weight: 400;
                                height: auto;line-height: 19.5px;border: 1px solid #ddd;background: #fafafa;padding: 1em;
                                margin: .5em 0;border-radius: 4px;">{{certfromresponse}}</div>';

    private $samlResponse = '<p style="font-weight:bold;font-size:14pt;margin-left:1%%;">SAML RESPONSE FROM IDP:</p><div style="color: #373B41;
                                font-family: Menlo,Monaco,Consolas,monospace;direction: ltr;text-align: left;white-space: pre;
                                word-spacing: normal;word-break: normal;font-size: 13px;font-style: normal;font-weight: 400;
                                height: auto;line-height: 19.5px;border: 1px solid #ddd;background: #fafafa;padding: 1em;
                                margin: .5em 0;border-radius: 4px;overflow:scroll">{{samlresponse}}</div>';

    private $footer = ' <div style="margin:3%%;display:block;text-align:center;">
                            <input style="padding:1%%;width:100px;background: #0091CD none repeat scroll 0%% 0%%;cursor: pointer;
                                font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;
                                    box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;
                                    color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';

    private $tableContent   = "<tr><td style='font-weight:bold;border:2px solid #949090;padding:2%%;'>{{key}}</td><td style='padding:2%%;
                                    border:2px solid #949090; word-wrap:break-word;'>{{value}}</td></tr>";


    public function __construct($attrs, SAMLResponseException $samlResponseException = null)
    {
        $this->attrs = $attrs;
        if(isset($attrs['NameID']))
        $this->nameId = $attrs["NameID"];
        $this->hasExceptionOccurred = Utilities::isBlank($samlResponseException) ? FALSE : TRUE;
        $this->samlException = $samlResponseException;
    }

    /**
     * Execute function to execute the classes function.
     */
    public function execute()
    {
        ob_clean();
        $this->processTemplateHeader();
        if(!$this->hasExceptionOccurred) $this->processTemplateContent();
        else $this->processExceptionTemplate();
        $this->processTemplateFooter();
        printf($this->template);
        return;
    }


    /**
     * Add header to our template variable for echoing on screen.
     */
    private function processTemplateHeader()
    {
        $header = Utilities::isBlank($this->nameId) ? $this->errorHeader : $this->successHeader;
        $header = str_replace("{{right}}","miniorange/sso/resources/images/right.png",$header);
        //var_dump($header);exit;
        $header = str_replace("{{wrong}}","miniorange/sso/resources/images/wrong.png",$header);
        $this->template = str_replace("{{header}}",$header,$this->template);
    }


    /**
     * Add exception Content to our template variable for echoing on screen.
     */
    private function processExceptionTemplate()
    {
        $this->exceptionBody = str_replace("{{exceptionmessage}}",$this->samlException->getMessage(),$this->exceptionBody);
        $this->exceptionBody = str_replace("{{certErrorDiv}}",$this->processCertErrors(),$this->exceptionBody);
        $response = $this->samlException instanceof SAMLResponseException ? $this->samlException->getSamlResponse() : "";
        $this->samlResponse = str_replace("{{samlresponse}}",$response,$this->samlResponse);
        $this->exceptionBody = str_replace("{{samlResponseDiv}}",$this->samlResponse,$this->exceptionBody);
        $this->template = str_replace("{{commonbody}}",$this->exceptionBody,$this->template);
    }


    /**
     * Add cert error and certificates for echoing on screen.
     */
    private function processCertErrors()
    {
        if($this->samlResponse instanceof SAMLResponseException && $this->samlException->isCertError())
        {
            $pluginCert = SAMLUtilities::sanitize_certificate($this->samlException->getPluginCert());
            $certFromIDP = SAMLUtilities::sanitize_certificate($this->samlException->getCertInResponse());
            $this->certError = str_replace("{{certinplugin}}",$pluginCert,$this->certError);
            $this->certError = str_replace("{{certfromresponse}}",$certFromIDP,$this->certError);
            return $this->certError;
        }
        return "";
    }


    /**
     * Add Content to our template variable for echoing on screen.
     */
    private function processTemplateContent()
    {
        $this->commonBody = str_replace("{{email}}",implode("/",$this->nameId),$this->commonBody);
        $tableContent = !array_filter($this->attrs) ? "No Attributes Received." : $this->getTableContent();
        $this->commonBody = str_replace("{{tablecontent}}",$tableContent,$this->commonBody);
        $this->template = str_replace("{{commonbody}}",$this->commonBody,$this->template);
    }


    /**
     * Append Attributes in the SAML response to the table
     * content to be shown to the user.
     */
    private function getTableContent()
    {
        $tableContent = '';
        foreach ($this->attrs as $key => $value)
        {
            if(!in_array(null, $value))
                $tableContent .= str_replace("{{key}}",$key,str_replace("{{value}}",
                    implode("<br/>",$value),$this->tableContent));
        }
        return $tableContent;
    }


    /**
     * Add footer to our template variable for echoing on screen.
     */
    private function processTemplateFooter()
    {
        $this->template = str_replace("{{footer}}",$this->footer,$this->template);
    }
}