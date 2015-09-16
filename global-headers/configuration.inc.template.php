<?php

ifndefdefine( 'APP_SHORT', 'terra' );
ifndefdefine( 'APP_ROOT', 'c:/www/' ); // Change this

// Paths
ifndefdefine( 'THIRD_PARTY_PATH', APP_ROOT . '3rd-party/');
ifndefdefine( 'RPC_CLASSES_PATH', APP_ROOT . 'rpc-classes/');
ifndefdefine( 'RPC_HEADERS_PATH', APP_ROOT . 'rpc-headers/');
ifndefdefine( 'CACHE_PATH', APP_ROOT . 'cache/' );
ifndefdefine( 'LOGS_PATH', APP_ROOT . 'logs/' );

// Database ifndefdefines
ifndefdefine('DATABASE_SERVER', '127.0.0.1');
ifndefdefine('DATABASE_NAME', APP_SHORT . 'db');
ifndefdefine('DATABASE_USERNAME', 'terrauser');   // Change this
ifndefdefine('DATABASE_PASSWORD', 'terrapwd');  // Change this
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
ifndefdefine('DMWL_MAX_AGE', 365);  // Worklist record expiration in days
ifndefdefine('DMWL_AE_TITLE', 'TERRA_DMWL');  // Change if desired
ifndefdefine('DMWL_PORT', 1070);  // Change if desired

// This shouldn't change.  Please refer to docs/README.md for instructions about installing DCMTk
ifndefdefine('DCMTK_BIN_PATH', THIRD_PARTY_PATH . 'dcmtk/bin/');

// XXX Should be removed eventually
require_once( RPC_HEADERS_PATH . 'globals.php' );
