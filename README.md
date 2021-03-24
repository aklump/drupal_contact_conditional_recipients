# Contact Conditional Recipients Drupal Module

## Summary

This module provides a means of making the recipient of a submitted contact form
be based on the value of a submitted field (on that form).  For example if you have a dropdown list of departments, the form can be routed based on the department chosen.  Use one form with multiple recipients.

## Installation

1. Download this module to _web/modules/custom/contact_conditional_recipients_.
1. Enable this module.

## Configuration

1. Create a contact form at _/admin/structure/contact/add_
1. Enter the "default" recipient(s) in the normal _Recipients_ field.  This default will be used (as the email recipient(s)) if/when the selected, submitted value does not match any of your configured conditional emails.
1. Add a _List_ field with multiple values, the values of which will be used to
   determine the recipient.
1. Visit _Manage display_ and enable _Conditional Recipients_ inside the _Custom Display
   Settings_ details element.
1. Select the _Conditional Recipients_ display mode tab.
1. Ensure the list field is not _Disabled_.
1. Set it's _label_ to `- Hidden -`.
1. Open the settings and enter a pipe-separated map, which follows this
   pattern `value|recipient(s)`; use `[contact:recipients]` for the form's
   default recipient (entered when creating the new form). Otherwise hard-code
   the recipient emails, e.g.,

    ```text
    Website|foo@bar.org,[contact:recipients]
    Sales|alpha@zulu.gov,bravo@zulu.gov
    ```

1. In this example, when the form is submitted and the chosen value is `Website`
   then the submission is delivered to `foo@bar.org` as well as the default
   recipients.
1. Otherwise, if the submitted value is `Sales`, then the form is only delivered
   to `alpha@zulu.gov`, and `bravo@zulu.gov`.
1. Finally, any other value that does not appear here will be delivered to the
   default recipients as configured at _
   /admin/structure/contact/manage/{form}_
1. Configuration is complete.

## Advanced Configuration

1. It is possible to use more than one field.  All matched values will be combined into the recipient list.  That means **if you have two fields and the first field matches for recipient A, and the second field matches for recipient B, then the email will be sent to both A and B.**
1. If no matched values then the default recipient will be used.
1. If any values are matched on any field, the default recipients will NOT be used except if `*|[contact:recipients]` has been include on at least one match.

## Usage

1. Try submitting the form with different list values to ensure proper delivery.
