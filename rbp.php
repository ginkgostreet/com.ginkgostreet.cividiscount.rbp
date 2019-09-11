<?php

require_once 'rbp.civix.php';
use CRM_Rbp_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function rbp_civicrm_config(&$config) {
  _rbp_civix_civicrm_config($config);

  static $listening;

  // we have to add a listener on the pre hook so we can prioritize ourselves over CiviDiscount
  if (!$listening) {
    \Civi::dispatcher()->addListener('hook_civicrm_pre', array('CRM_Rbp_Util', 'deleteParticipant'), 250);
    $listening = TRUE;
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function rbp_civicrm_xmlMenu(&$files) {
  _rbp_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function rbp_civicrm_install() {
  _rbp_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function rbp_civicrm_postInstall() {
  _rbp_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function rbp_civicrm_uninstall() {
  _rbp_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function rbp_civicrm_enable() {
  _rbp_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function rbp_civicrm_disable() {
  _rbp_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function rbp_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _rbp_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function rbp_civicrm_managed(&$entities) {
  _rbp_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function rbp_civicrm_caseTypes(&$caseTypes) {
  _rbp_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function rbp_civicrm_angularModules(&$angularModules) {
  _rbp_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function rbp_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _rbp_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_check().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_check/
 */
function rbp_civicrm_check(&$messages) {
  if (!CRM_Extension_System::singleton()->getMapper()->isActiveModule('cividiscount')) {
    $name = 'rbp.dependency.cividiscount';
    $content = E::ts('Extension %1 depends on %2. No harm can come from having it installed without CiviDiscount, but neither can any good.', array(1 => E::LONG_NAME, 2 => 'org.civicrm.module.cividiscount'));
    $title = E::ts('Missing Dependency');
    $severity = \Psr\Log\LogLevel::WARNING;
    $icon = 'fa-plug';

    $messages[] = new CRM_Utils_Check_Message($name, $content, $title, $severity, $icon);
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post/
 */
function rbp_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  $function = '_' . __FUNCTION__ . '_' . $objectName;
  if (is_callable($function)) {
    $function($op, $objectId, $objectRef);
  }
}

/**
 * (Delegated) implementation of hook_civicrm_post().
 */
function _rbp_civicrm_post_DiscountTrack($op, $id, CRM_CiviDiscount_DAO_Track &$discountTrack) {
  if ($op !== 'create' || !CRM_Rbp_Util::isRbpEnabled($discountTrack->item_id)) {
    return;
  }

  $participantCount = CRM_Rbp_Util::getParticipantCount($discountTrack);

  // By the time the post hook fires, CiviDiscount has already incremented the
  // usage, so we subtract one.
  $usage = $participantCount - 1;

  if ($usage) {
    // Why do we fetch first, and why via DAO rather than API? First of all, we
    // need the current usage for our calculation. As for the mechanism,
    // api.DiscountCode.create is wacky; on update it nulls several fields if
    // they aren't supplied as params. Moreover, it doesn't accept count_use as
    // a param.
    $dao = CRM_CiviDiscount_DAO_Item::findById($discountTrack->item_id);
    $dao->count_use += $usage;
    $dao->save();
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm/
 */
function rbp_civicrm_buildForm($formName, &$form) {
  $function = '_' . __FUNCTION__ . '_' . $formName;
  if (is_callable($function)) {
    $function($form);
  }
}

/**
 * (Delegated) implementation of hook_civicrm_buildForm().
 */
function _rbp_civicrm_buildForm_CRM_CiviDiscount_Form_Admin(CRM_CiviDiscount_Form_Admin &$form) {
  // add the checkbox to the form with the appropriate default value
  $discountCodeId = $form->getVar('_id');
  $form->_defaultValues['is_rbp_enabled'] = ($discountCodeId && CRM_Rbp_Util::isRbpEnabled($discountCodeId));
  $form->add('checkbox', 'is_rbp_enabled', E::ts('Increase usage count for each participant rather than for each transaction?'));

  // add the checkbox to the display and position it
  CRM_Core_Region::instance('page-body')->add(array(
    'template' => 'CRM/CiviDiscount/Form/Admin/is_rbp_enabled.tpl',
  ));
  Civi::resources()->addScriptFile(E::LONG_NAME, 'js/CRM/CiviDiscount/Form/Admin/is_rbp_enabled.js');
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_validateForm
 */
function rbp_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $function = '_' . __FUNCTION__ . '_' . $formName;
  if (is_callable($function)) {
    $function($fields, $files, $form, $errors);
  }
}

/**
 * (Delegated) implementation of hook_civicrm_postProcess().
 */
function _rbp_civicrm_validateForm_CRM_Event_Form_Registration_Register(&$fields, &$files, &$form, &$errors) {

  // similar to cividiscount_civicrm_validateForm except we add the line items participant count  

  // _discountInfo is assigned in cividiscount_civicrm_buildAmount() or
  // cividiscount_civicrm_membershipTypeValues() when a discount is used.
  $discountInfo = $form->get('_discountInfo');
  if (isset($discountInfo['discount']['id'])) {
    $discount = $discountInfo['discount'];

    if (CRM_Rbp_Util::isRbpEnabled($discount['id'])) {

      if ($discount['count_max'] > 0) {
        $apcount = CRM_Rbp_Util::getSelectedParticipantCount($form);

        // FIXME: we should check the price option for additional_participants too
        if (array_key_exists('additional_participants', $sv)) {
          $apcount += $sv['additional_participants'];
        }
        if (($discount['count_use'] + $apcount) > $discount['count_max']) {
          $errors['discountcode'] = ts('There are not enough uses remaining for this code.');
        }
      }
    }
  }

}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 */
function rbp_civicrm_postProcess($formName, &$form) {
  $function = '_' . __FUNCTION__ . '_' . $formName;
  if (is_callable($function)) {
    $function($form);
  }
}

/**
 * (Delegated) implementation of hook_civicrm_postProcess().
 */
function _rbp_civicrm_postProcess_CRM_CiviDiscount_Form_Admin(CRM_CiviDiscount_Form_Admin &$form) {
  $discountCodeId = $form->getVar('_id');

  // in the case of a create, the ID isn't on the form and we have to look it up
  if (!$discountCodeId) {
    $discountCodeId = civicrm_api3('DiscountCode', 'getvalue', array(
      'code' => $form->_submitValues['code'],
      'return' => 'id',
    ));
  }

  if (CRM_Utils_Array::value('is_rbp_enabled', $form->_submitValues)) {
    CRM_Rbp_Util::enableRbp($discountCodeId);
  }
  else {
    CRM_Rbp_Util::disableRbp($discountCodeId);
  }
}
