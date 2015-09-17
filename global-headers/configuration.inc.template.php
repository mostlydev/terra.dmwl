<?php

/*
 * ALL PATHS MUST HAVE TRAILING SLASHES
 */

ifndefdefine( 'APP_SHORT', 'terra' ); //TODO: Check and/or change if using a different app and set of classes
ifndefdefine( 'APP_ROOT', realpath(realpath(dirname(__FILE__)) . '/..') . '/' );

// Paths
ifndefdefine( 'THIRD_PARTY_PATH', APP_ROOT . '3rd-party/');
ifndefdefine( 'RPC_CLASSES_PATH', APP_ROOT . 'rpc-classes/');
ifndefdefine( 'RPC_HEADERS_PATH', APP_ROOT . 'rpc-headers/');
ifndefdefine( 'CACHE_PATH', APP_ROOT . 'cache/' );
ifndefdefine( 'LOGS_PATH', APP_ROOT . 'logs/' );

// Database ifndefdefines
ifndefdefine('DATABASE_SERVER', '127.0.0.1'); //TODO: Check/change
ifndefdefine('DATABASE_NAME', APP_SHORT . 'db'); //TODO: Check/change
ifndefdefine('DATABASE_USERNAME', APP_SHORT. 'user');  //TODO: Check/change
ifndefdefine('DATABASE_PASSWORD', APP_SHORT . 'pwd');  //TODO: Check/change
ifndefdefine('TABLE_PREFIX','');

// Logging
require_once( RPC_CLASSES_PATH . 'GenericHelper.php' );
ifndefdefine('LOGGING_LEVEL',
  GenericHelper::LOG_INFO | GenericHelper::LOG_DEBUG
);

// Debug mode
@include('override-debug.inc.php');
ifndefdefine( 'FLUX_INC_DEBUG_MODE', false );

// Php config
date_default_timezone_set( 'America/New_York' );

ifndefdefine('DMWL_DCM_PATH', CACHE_PATH . 'worklist/');
ifndefdefine('DMWL_MAX_AGE', 7);  // TODO: Worklist record expiration in days
ifndefdefine('DMWL_AE_TITLE', strtoupper(APP_SHORT) . '_DMWL');  //TODO: Check/change
ifndefdefine('DMWL_PORT', 1070);  // TODO: You need to elevate to root to listen on ports lower than 1024
ifndefdefine('DMWL_SOURCE_CLASS', ucfirst(APP_SHORT) . 'Exam'); // TODO: Make sure it matches the data class name!

// This shouldn't change.  Please refer to README.md for instructions about installing DCMTk
ifndefdefine('DCMTK_BIN_PATH', THIRD_PARTY_PATH . 'dcmtk/bin/');

// XXX Should be removed eventually
require_once( RPC_HEADERS_PATH . 'globals.php' );
