<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller']   = 'auth';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login']          = 'auth/login';
$route['logout']         = 'auth/logout';
$route['register']       = 'auth/register';

// Main pages
$route['dashboard']                = 'dashboard/index';
$route['payment']                  = 'payment/index';
$route['payment/all']              = 'payment/all';
$route['payment/submit']           = 'payment/submit';
$route['payment/update_status']    = 'payment/update_status';

// Expense — specific routes BEFORE catch-all
$route['expense']                  = 'expense/index';
$route['expense/create']           = 'expense/create';
$route['expense/edit/(:any)']      = 'expense/edit/$1';
$route['expense/approve/(:any)']   = 'expense/approve/$1';
$route['expense/reject/(:any)']    = 'expense/reject/$1';
$route['expense/complete/(:any)']  = 'expense/complete/$1';
$route['expense/pending/(:any)']   = 'expense/pending/$1';
$route['expense/(:any)']           = 'expense/detail/$1';

$route['fund']                     = 'fund/index';
$route['fund/adjust']              = 'fund/adjust';
$route['fund/delete/(:num)']       = 'fund/delete/$1';

$route['students']                 = 'students/index';
$route['students/update_payment']  = 'students/update_payment';

$route['notifications']            = 'notifications/index';

$route['settings']                 = 'settings/index';
$route['settings/save']            = 'settings/save';
$route['settings/save_user']       = 'settings/save_user';
$route['settings/reset_pass']      = 'settings/reset_pass';

// Admin
$route['admin/students']             = 'admin/students';
$route['admin/add_student']          = 'admin/add_student';
$route['admin/edit_student']         = 'admin/edit_student';
$route['admin/delete_student']       = 'admin/delete_student';
$route['admin/import_students']      = 'admin/import_students';
$route['admin/import_students_json'] = 'admin/import_students_json';
$route['admin/payments']             = 'admin/payments';
$route['admin/generate_month']       = 'admin/generate_month';
$route['admin/mark_overdue']         = 'admin/mark_overdue';
$route['admin/recalc_penalties']     = 'admin/recalc_penalties';
$route['admin/clear_transactions']   = 'admin/clear_transactions';
$route['admin/clear_students']         = 'admin/clear_students';
$route['admin/get_month_detail']       = 'admin/get_month_detail';
$route['admin/update_payment_record']  = 'admin/update_payment_record';
$route['admin/seed_january']           = 'admin/seed_january';
$route['admin/users']                = 'admin/users';
$route['admin/add_user']             = 'admin/add_user';
$route['admin/edit_user']            = 'admin/edit_user';
$route['admin/toggle_user']          = 'admin/toggle_user';
$route['admin/delete_user']          = 'admin/delete_user';

// Profile
$route['profile']                    = 'profile/index';
$route['profile/change_password']    = 'profile/change_password';

// Reports
$route['reports']                    = 'reports/index';

// Standalone public payment form (no login)
$route['pay']                      = 'pay/index';
$route['pay/lookup']               = 'pay/lookup';
$route['pay/submit']               = 'pay/submit';

// API (AJAX endpoints)
$route['api/(:any)']               = 'api/$1';
