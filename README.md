# Branch: select2-19867 #

This branch enhances `wp_dropdown_users()` with Select2 when there are many available users to choose from, thus potentially overloading the dropdown. See Trac ticket [#19867](https://core.trac.wordpress.org/ticket/19867).

To turn it on for an install that does not have a large number of users, try:

`add_filter( 'admin_user_dropdown_autocomplete', '__return_true' );`.
