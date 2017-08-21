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
function _rbp_civicrm_post_DiscountTrack($op, $id, &$discountTrack) {
  if ($op !== 'create') {
    return;
  }

  $participantCount = CRM_Rbp_Util::getParticipantCount($discountTrack);

  // By the time the post hook fires, CiviDiscount has already incremented the
  // usage, so we subtract one.
  $usage = $participantCount - 1;

  if ($usage) {
    civicrm_api3('DiscountCode', 'create', array(
      'id' => $discountTrack->item_id,
      'count_use' => $usage,
    ));
  }
}
