<?php
/**
 * Receiver.php
 *
 * @module Email
 * @package Email\Model
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

namespace Email\Model;


use MVC\DataType\DTArrayObject;
use MVC\DataType\DTKeyValue;
use MVC\Event;
use MVC\Helper;
use MVC\Log;
use MVC\Registry;

/**
 * @deprecated
 * @author Guido Üffing <guido.ueffing@mediafinanz.de>
 */
class Receiver
{
    /**
     * @deprecated Bitte nicht nutzen. Existiert allein noch zu Doku-Zwecken
     * @param $oEmail
     * @return bool|null
     * @throws \ReflectionException
     */
    public static function sendToInkosReceiver($oEmail)
    {
        Helper::STOP('@deprecated Bitte nicht nutzen. Existiert allein noch zu Doku-Zwecken');

        $bSuccess = null;
        $aEmailRecipient = $oEmail->get_recipientMailAdresses();

        foreach ($aEmailRecipient as $sEmailRecipient)
        {
            $aContent = array(
                'sKey' => Registry::get('MODULE_EMAIL_CONFIG')['sEmailServiceKey'],
                'sSubject' => $oEmail->get_subject(),
                'sMessage' => (false === empty($oEmail->get_html()) ? $oEmail->get_html() : $oEmail->get_text()),
                'sMessageAlt' => $oEmail->get_text(),
                'sSenderEmail' => $oEmail->get_senderMail(),
                'sRecipientEmail' => $sEmailRecipient,
                'sRecipientName' => '',
                'aAttachment' => base64_encode(serialize(Index::getAttachmentArray($oEmail))),
            );
            Log::WRITE($aContent, 'debug.log');

            // sende Mail
            $sResponse = file_get_contents(

                Registry::get('MODULE_EMAIL_CONFIG')['sEmailServiceUrl'],
                false,
                stream_context_create(array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'timeout' => Registry::get('MODULE_EMAIL_CONFIG')['iEmailServiceTimeout'],
                        'method' => 'POST',
                        'content' => http_build_query($aContent),
                    )
                ))
            );

            $aResponse = json_decode($sResponse, true);
            (null === $bSuccess && isset($aResponse['bSuccess'])) ? $bSuccess = $aResponse['bSuccess'] : false;

            Event::RUN(
                'email.model.index.send.response',
                DTArrayObject::create()->add_aKeyValue(DTKeyValue::create()->set_sKey('sResponse')->set_sValue($sResponse))
            );
        }

        return (null === $bSuccess) ? false : $bSuccess;
    }
}
