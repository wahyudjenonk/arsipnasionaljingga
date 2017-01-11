<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'controllerx';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Routing Core
$route['backoffice'] = 'controllerx';
$route['backoffice-masuk'] = 'login';
$route['backoffice-keluar'] = 'login/logout';
$route['backoffice-Grid/(:any)'] = 'controllerx/get_grid/$1';
$route['backoffice-GetDataChart'] = 'controllerx/get_chart';
$route['backoffice-Status/(:any)'] = 'controllerx/set_flag/$1';
$route['backoffice-form/(:any)'] = 'controllerx/get_form/$1';
$route['backoffice-Data/(:any)'] = 'controllerx/getdata/$1';
$route['backoffice-Report/(:any)'] = 'controllerx/get_report/$1';
$route['backoffice-simpan/(:any)'] = 'controllerx/simpandata/$1';
$route['backoffice-getmodul/(:any)/(:any)'] = 'controllerx/modul/$1/$2';

$route['beranda'] = 'controllerx/modul/beranda/main';
$route['backoffice-gettotalarsip'] = 'controllerx/modul/beranda/total_arsip';
$route['backoffice-gettotalarsiphtml'] = 'controllerx/modul/beranda/total_arsip_html';






