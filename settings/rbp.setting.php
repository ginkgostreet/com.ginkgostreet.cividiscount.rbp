<?php

use CRM_Rbp_ExtensionUtil as E;

return array(
  'rbp_enabled_discount_codes' => array(
    'group_name' => 'Redeem by Participant (CiviDiscount)',
    'group' => E::LONG_NAME,
    'name' => 'rbp_enabled_discount_codes',
    'type' => 'Array',
    'default' => array(),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Contains a list of CiviDiscount IDs which are configured to manage usage by participant count rather than transaction count'),
  ),
);
