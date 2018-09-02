<?php
/**
 * @copyright 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
 * @author Jan Dageförde <jan.dagefoerde@uni-muenster.de>

 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\Moodle\Controller;

use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Controller;

class CheckSetupController extends Controller {
	private $userId;
    /**
     * @var IConfig
     */
    private $config;
    /**
     * @var ClientMapper
     */
    private $clientMapper;

    public function __construct($AppName,
                                IConfig $config,
                                ClientMapper $clientMapper,
                                IRequest $request,
                                $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
        $this->config = $config;
        $this->clientMapper = $clientMapper;
    }

    /**
     * @return DataResponse
     */
    public function checkSupportsBearerToken() {
        $response = array();
        // Check basic sharing settings.
        $settings = [
            'publicSharesEnabled' => $this->config->getAppValue('core', 'shareapi_enabled', 'yes'),
            'publicSharesExpire' => $this->config->getAppValue('core', 'shareapi_default_expire_date', 'no'),
            'publicSharesExpireEnforced' => $this->config->getAppValue('core', 'shareapi_enforce_expire_date', 'no'),
        ];
        $response['sharingEnabled'] = $settings['publicSharesEnabled'] === 'yes';
        $response['sharesNeverExpire'] = $settings['publicSharesExpire'] === 'no'; // Unless #10178 resolved.
        $response['shareExpirationNotEnforced'] = $settings['publicSharesExpire'] === 'no' ||
            $settings['publicSharesExpireEnforced'] === 'no';

        // Check that at least one configured OAuth client's URI ends with /admin/oauth2callback.php.
        $validClients = array();
        $clients = $this->clientMapper->getClients();
        $expectedEnding = '/admin/oauth2callback.php';
        foreach ($clients as $client) {
            if (mb_substr($client->getRedirectUri(), -mb_strlen($expectedEnding)) === $expectedEnding) {
                $validClients[] = $client->getName();
            } else {
                // Client not compatible.
            }
        }
        $response['validClients'] = $validClients;

        // Check is Nextcloud over https?
        // -> Client-side check.

        // Check whether header is dropped.
        if (isset($_SERVER['HTTP_AUTHENTICATION']) &&
            $_SERVER['HTTP_AUTHENTICATION'] === 'Bearer xyz') {
             $response['supportsBearerToken'] = true;
        } else {
            // Bearer authentication token was not transmitted.
            $response['supportsBearerToken'] = false;
        }
        return new DataResponse($response);
    }

}
