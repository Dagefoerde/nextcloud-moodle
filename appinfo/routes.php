<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Moodle\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        ['name' => 'CheckSetup#checkSupportsBearerToken', 'url' => '/settings/checksupportsbearertoken', 'verb' => 'GET'],
        ['name' => 'SystemAccount#addSystemAccount', 'url' => '/settings/addsystemaccount', 'verb' => 'POST'],
        ['name' => 'SystemAccount#removeSystemAccount', 'url' => '/settings/removesystemaccount', 'verb' => 'POST'],
        ['name' => 'SystemAccount#getSystemAccounts', 'url' => '/settings/systemaccounts', 'verb' => 'GET'],
    ]
];
