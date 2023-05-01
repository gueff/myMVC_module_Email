<?php

/**
 * Smtp.php
 *
 * @module Email
 * @package Email\Model
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

namespace Email\Model;

use Email\DataType\Email;
use MVC\Config;
use MVC\DataType\DTArrayObject;
use MVC\DataType\DTKeyValue;
use MVC\Event;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Smtp
{
    /**
     * @param Email $oEmail
     * @return DTArrayObject
     * @throws \ReflectionException
     */
    public static function sendViaPhpMailer(Email $oEmail)
    {
        /** @var boolean $bSuccess */
        $bSuccess = false;
        $oException = null;

        try {

            $oPHPMailer = new PHPMailer(true);

            // Specify the SMTP settings.
            $oPHPMailer->isSMTP();
            $oPHPMailer->CharSet    = 'UTF-8';
            $oPHPMailer->Encoding   = 'base64';

            $oPHPMailer->Username   = Config::MODULE('Email')['sUsername'];
            $oPHPMailer->Password   = Config::MODULE('Email')['sPassword'];
            $oPHPMailer->Host       = Config::MODULE('Email')['sHost'];
            $oPHPMailer->Port       = Config::MODULE('Email')['iPort'];
            $oPHPMailer->SMTPAuth   = Config::MODULE('Email')['bAuth'];
            $oPHPMailer->SMTPSecure = Config::MODULE('Email')['sSecure'];

            // Specify the content of the message.
            $oPHPMailer->setFrom(
                $oEmail->get_senderMail(),
                $oEmail->get_senderName()
            );
            $oPHPMailer->Subject    = $oEmail->get_subject();
            $oPHPMailer->isHTML(true);
            $oPHPMailer->Body       = $oEmail->get_html();
            $oPHPMailer->AltBody    = $oEmail->get_text();

            // Recipients
            /** @var string $sEmailRecipient */
            foreach ($oEmail->get_recipientMailAdresses() as $sEmailRecipient)
            {
                $oPHPMailer->addAddress($sEmailRecipient);
            }

            // Attachments
            /** @var array $aDTArrayObject */
            foreach ($oEmail->get_oAttachment() as $aDTArrayObject)
            {
                /** @var array $aDTKeyValue */
                foreach ($aDTArrayObject as $aDTKeyValue)
                {
                    $oDTKeyValue = DTKeyValue::create($aDTKeyValue);
                    $oPHPMailer->addAttachment(
                        $oDTKeyValue->get_sValue()['file'],
                        $oDTKeyValue->get_sValue()['name']
                    );
                }
            }

            $bSuccess = $oPHPMailer->Send();
            $sMessage = json_encode($bSuccess);

        } catch (phpmailerException $oException) {

            $bSuccess = false;
            $sMessage = $oException->getMessage();

            Event::run ('mvc.error',
                DTArrayObject::create()
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sMessage')->set_sValue($oException->getMessage()))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('oException')->set_sValue($oException))
            );

        } catch (Exception $oException) {

            $bSuccess = false;
            $sMessage = $oException->getMessage();

            Event::run ('mvc.error',
                DTArrayObject::create()
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sMessage')->set_sValue($oException->getMessage()))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('oException')->set_sValue($oException))
            );
        }

        $oResponse = DTArrayObject::create()
            ->add_aKeyValue(DTKeyValue::create()->set_sKey('bSuccess')->set_sValue($bSuccess))
            ->add_aKeyValue(DTKeyValue::create()->set_sKey('sMessage')->set_sValue($sMessage))
            ->add_aKeyValue(DTKeyValue::create()->set_sKey('oException')->set_sValue($oException));

        Event::run('email.model.index.send.response', $oResponse);

        return $oResponse;
    }
}