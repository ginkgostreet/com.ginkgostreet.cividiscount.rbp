<?php

class CRM_Rbp_Util {

  public static function getParticipantCount(CRM_CiviDiscount_DAO_Track $discountTrack) {
    $lineItems = civicrm_api3('LineItem', 'get', array(
      'contribution_id' => $discountTrack->contribution_id,
    ));

    return (int) array_sum(array_column($lineItems['values'], 'participant_count'));
  }

  /**
   * @param int|string $discountCodeId
   * @return boolean
   */
  public static function isRbpEnabled($discountCodeId) {
    $id = CRM_Utils_Type::validate($discountCodeId, 'Int');

    $enabled = Civi::settings()->get('rbp_enabled_discount_codes');
    return in_array($id, $enabled);
  }

  /**
   * Marks a DiscountCode as not configured for Redeem by Participant, i.e., not
   * of interest to this extension.
   *
   * If already not configured, does nothing.
   *
   * @param int|string $discountCodeId
   */
  public static function disableRbp($discountCodeId) {
    $id = CRM_Utils_Type::validate($discountCodeId, 'Int');

    $enabled = Civi::settings()->get('rbp_enabled_discount_codes');
    if (in_array($id, $enabled)) {
      $key = array_search($id, $enabled);
      unset($enabled[$key]);
      Civi::settings()->set('rbp_enabled_discount_codes', $enabled);
    }
  }

  /**
   * Marks a DiscountCode as configured for Redeem by Participant.
   *
   * If already configured, does nothing.
   *
   * @param int|string $discountCodeId
   */
  public static function enableRbp($discountCodeId) {
    $id = CRM_Utils_Type::validate($discountCodeId, 'Int');

    $enabled = Civi::settings()->get('rbp_enabled_discount_codes');
    if (!in_array($id, $enabled)) {
      $enabled[] = $id;
      sort($enabled, SORT_NUMERIC);
      Civi::settings()->set('rbp_enabled_discount_codes', $enabled);
    }
  }

}
