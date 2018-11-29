<?php

class CRM_Rbp_Util {

  public static function deleteParticipant($event, $eventName, $dispatcher) {

    if ($event->action == 'delete' && $event->entity == 'Participant') {
      try {
        $track = civicrm_api3('DiscountTrack', 'getsingle', array(
          'entity_table' => 'civicrm_participant',
          'entity_id' => $event->id,
        ));

        if (!CRM_Rbp_Util::isRbpEnabled($track['item_id'])) {
          return;
        }

        // we have to do this because ::getParticipantCount has the arg typed
        $discountTrack = new CRM_CiviDiscount_DAO_Track();
        foreach ($track as $prop => $val) {
          $discountTrack->$prop = $val;
        }

        $participantCount = CRM_Rbp_Util::getParticipantCount($discountTrack);

        // CiviDiscount will decrement by one, so adjust for it
        $usage = $participantCount - 1;

        if ($usage) {
          // Why do we fetch first, and why via DAO rather than API? First of all, we
          // need the current usage for our calculation. As for the mechanism,
          // api.DiscountCode.create is wacky; on update it nulls several fields if
          // they aren't supplied as params. Moreover, it doesn't accept count_use as
          // a param.
          $dao = CRM_CiviDiscount_DAO_Item::findById($discountTrack->item_id);
          $dao->count_use -= $usage;
          $dao->save();
        }
      }
      catch (CiviCRM_API3_Exception $e) {
        // if we land here then this participant wasn't discounted
      }
    }
  }

  /**
   * Gets the participant count associated with a given usage of a discount code.
   *
   * @param CRM_CiviDiscount_DAO_Track $discountTrack
   * @return int
   */
  public static function getParticipantCount(CRM_CiviDiscount_DAO_Track $discountTrack) {
    $lineItems = civicrm_api3('LineItem', 'get', array(
      'entity_table' => $discountTrack->entity_table,
      'entity_id' => $discountTrack->entity_id,
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
