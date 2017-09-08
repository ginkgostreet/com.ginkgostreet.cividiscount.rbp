# com.ginkgostreet.cividiscount.rbp
Redeem by Participant (CiviDiscount)

Presently, the usage count for a discount program is based on transactions rather than admissions. For example, suppose
Acme Corp has 100 passes to disburse for an event. For a transaction in which a 100% discount code is used to purchase
admission for 25 students, CiviDiscount would show that 99 passes remain, where we would expect that only 75 passes remain.

Note: This extension has been tested for use cases where the number of participants is configured in a price set. There have
been reports that using CiviEvent's "Register multiple participants" feature (found on the "Online Registration" tab of an
event config) already provides the desired behavior, and that this extension interferes with that. In the short term, site
administrators can resolve this conflict by simply unchecking the "Increase usage count for each participant rather than
for each transaction?" box in the Discount configuration for events which have "Register multiple participants" enabled.
In the long term, the extension can "bail out" if the right conditions are met so as not to interfere with the default
behavior -- patch or funding welcome.

## Technical Approach
In broad strokes this extension:
* Listens for creation of DiscountTrack entities on hook_civicrm_post. (This indicates a pass was used.)
* Looks up the contribution record linked through this entity to determine how many participants were registered.
* Calculates a new value for the `count_use` field for the related DiscountCode and updates it.
