<?php

class CRM_Rbp_Util {

  public static function getParticipantCount(CRM_CiviDiscount_DAO_Track $discountTrack) {
    $lineItems = civicrm_api3('LineItem', 'get', array(
      'contribution_id' => $discountTrack->contribution_id,
    ));

    return (int) array_sum(array_column($lineItems['values'], 'participant_count'));
  }

}
