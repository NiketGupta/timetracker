<?php
// +----------------------------------------------------------------------+
// | Anuko Time Tracker
// +----------------------------------------------------------------------+
// | Copyright (c) Anuko International Ltd. (https://www.anuko.com)
// +----------------------------------------------------------------------+
// | LIBERAL FREEWARE LICENSE: This source code document may be used
// | by anyone for any purpose, and freely redistributed alone or in
// | combination with other software, provided that the license is obeyed.
// |
// | There are only two ways to violate the license:
// |
// | 1. To redistribute this code in source form, with the copyright
// |    notice or license removed or altered. (Distributing in compiled
// |    forms without embedded copyright notices is permitted).
// |
// | 2. To redistribute modified versions of this code in *any* form
// |    that bears insufficient indications that the modifications are
// |    not the work of the original author(s).
// |
// | This license applies to this document only, not any other software
// | that it may be combined with.
// |
// +----------------------------------------------------------------------+
// | Contributors:
// | https://www.anuko.com/time_tracker/credits.htm
// +----------------------------------------------------------------------+

require_once('initialize.php');
import('form.Form');
import('form.ActionForm');
import('ttUserConfig');
import('ttReportHelper');

// Access check.
if (!(ttAccessAllowed('view_own_reports') || ttAccessAllowed('view_reports'))) {
  header('Location: access_denied.php');
  exit();
}

$uc = new ttUserConfig();

if ($request->isPost()) {
  $cl_receiver = trim($request->getParameter('receiver'));
  $cl_cc = trim($request->getParameter('cc'));
  $cl_subject = trim($request->getParameter('subject'));
  $cl_comment = trim($request->getParameter('comment'));
} else {
  $cl_receiver = $uc->getValue(SYSC_LAST_REPORT_EMAIL);
  $cl_cc = $uc->getValue(SYSC_LAST_REPORT_CC);
  $cl_subject = $i18n->get('form.mail.report_subject');
}

$form = new Form('mailForm');
$form->addInput(array('type'=>'text','name'=>'receiver','style'=>'width: 300px;','value'=>$cl_receiver));
$form->addInput(array('type'=>'text','name'=>'cc','style'=>'width: 300px;','value'=>$cl_cc));
$form->addInput(array('type'=>'text','name'=>'subject','style'=>'width: 300px;','value'=>$cl_subject));
$form->addInput(array('type'=>'textarea','name'=>'comment','maxlength'=>'250','style'=>'width: 300px; height: 60px;'));
$form->addInput(array('type'=>'submit','name'=>'btn_send','value'=>$i18n->get('button.send')));

if ($request->isPost()) {
  // Validate user input.
  if (!ttValidEmailList($cl_receiver)) $err->add($i18n->get('error.field'), $i18n->get('form.mail.to'));
  if (!ttValidEmailList($cl_cc, true)) $err->add($i18n->get('error.field'), $i18n->get('label.cc'));
  if (!ttValidString($cl_subject)) $err->add($i18n->get('error.field'), $i18n->get('label.subject'));
  if (!ttValidString($cl_comment, true)) $err->add($i18n->get('error.field'), $i18n->get('label.comment'));

  if ($err->no()) {
    // Save last report emails for future use.
    $uc->setValue(SYSC_LAST_REPORT_EMAIL, $cl_receiver);
    $uc->setValue(SYSC_LAST_REPORT_CC, $cl_cc);

    // Obtain session bean with report attributes.
    $bean = new ActionForm('reportBean', new Form('reportForm'));
    $options = ttReportHelper::getReportOptions($bean);

    // Prepare report body.
    $body = ttReportHelper::prepareReportBody($options, $cl_comment);

    import('mail.Mailer');
    $mailer = new Mailer();
    $mailer->setCharSet(CHARSET);
    $mailer->setContentType('text/html');
    $mailer->setSender(SENDER);
    $mailer->setReceiver($cl_receiver);
    if (isset($cl_cc))
      $mailer->setReceiverCC($cl_cc);
    if (!empty($user->bcc_email))
      $mailer->setReceiverBCC($user->bcc_email);
    $mailer->setMailMode(MAIL_MODE);
    if ($mailer->send($cl_subject, $body))
      $msg->add($i18n->get('form.mail.report_sent'));
    else
      $err->add($i18n->get('error.mail_send'));
  }
}

$smarty->assign('sender', SENDER);
if (function_exists('imap_mime_header_decode')) {
  $elements = imap_mime_header_decode(SENDER);
  if (count($elements) > 1) {
      // Reassign sender.
      $new = $elements[count($elements) - 2]->text;
      $smarty->assign('sender', $elements[count($elements) - 2]->text);
  }
}

$smarty->assign('title', $i18n->get('title.send_report'));
$smarty->assign('forms', array($form->getName()=>$form->toArray()));
$smarty->assign('onload', 'onLoad="document.mailForm.'.($cl_receiver?'comment':'receiver').'.focus()"');
$smarty->assign('content_page_name', 'mail.tpl');
$smarty->display('index.tpl');
