<?php

// Actions *****************************************************************/

add_action( 'user_register', 'erp_hr_new_admin_as_manager' );
add_action( 'delete_user', 'erp_hr_employee_on_delete' );
add_action( 'set_user_role', 'erp_hr_existing_role_to_employee', 10, 2 );

// After create employee apply leave policy
add_action( 'erp_hr_employee_new', 'erp_hr_apply_policy_on_new_employee', 10, 1 );
add_action( 'erp_daily_scheduled_events', 'erp_hr_apply_scheduled_policies' );
add_action( 'erp_daily_scheduled_events', 'erp_hr_schedule_check_todays_birthday' );
add_action( 'erp_daily_scheduled_events', 'erp_hr_schedule_check_todays_work_anniversary' );
add_action( 'erp_daily_scheduled_events', 'erp_hr_apply_entitlement_yearly' );
add_action( 'erp_hr_leave_policy_new', 'erp_hr_apply_policy_existing_employee', 10, 2 );
add_action( 'erp_hr_schedule_announcement_email', 'erp_hr_send_announcement_email', 10, 2 );

// Filters *****************************************************************/
add_filter( 'erp_map_meta_caps', 'erp_hr_map_meta_caps', 10, 4 );
add_filter( 'editable_roles', 'erp_hr_filter_editable_roles' );
add_filter( 'woocommerce_prevent_admin_access', 'erp_hr_wc_prevent_admin_access' );
add_filter( 'erp_login_redirect', 'erp_hr_login_redirect', 10, 2 );
add_filter( 'erp_hr_employee_restricted_data', 'erp_hr_control_restricted_data', 10, 2 );
add_filter( 'erp_mail_recipients', 'erp_hr_exclude_recipients');
add_filter( 'erp_hr_get_employee_fields', 'get_employee_additional_fields', 10, 3 );

// Send Weekly digest email to HR manager
add_action( 'erp_daily_scheduled_events', 'send_weekly_digest_email_to_hr' );

// Send Birthday email wish to employee
add_action( 'erp_hr_happened_birthday_today', 'erp_hr_send_birthday_wish_email', 10, 1 );
