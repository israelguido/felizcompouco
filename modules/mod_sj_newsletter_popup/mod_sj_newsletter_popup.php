<?php
/**
 * @package Sj Newletter Popup
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;
/*-- Process---*/
$layout = $params->get('layout', 'default');
$intro = $params->get('intro_text', '');
$footer = $params->get('footer_text', '');
$subject = $params->get('email_template_subject');
$content_email = $params->get('content_email');
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if ($is_ajax && isset($_POST['is_newletter']) && $_POST['is_newletter']) {
    $email = $_POST['email'];
    // Send email
    $mailer = JFactory::getMailer();
    $config = JFactory::getConfig();
    $from = $config->get('mailfrom');
    $fromname = $config->get('fromname');
    $sender = array(
        $from,
        $fromname
    );
    $mailer->setSender($sender);
    $mailer->addRecipient($email);
    $mailer->Subject = $subject;

    $mailer->isHTML(true);
    $mailer->setBody($content_email);
    $send = $mailer->Send();
    if ($send !== true) {
        echo 'Error sending email: ' . $send->__toString();
    } else {
        echo 'Successful! Thank you for booking ';
    }
}
require JModuleHelper::getLayoutPath($module->module, $layout);

?>
