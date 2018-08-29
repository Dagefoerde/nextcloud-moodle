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

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\IUserManager;

class SystemAccountController extends Controller {
	private $userId;
    private $userManager;
    private $config;

    public function __construct($AppName, IRequest $request, IConfig $config, IUserManager $userManager, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->config = $config;
		$this->userManager = $userManager;
	}

    /**
     * @param string $uid
     * @return DataResponse
     */
    public function addSystemAccount($uid) {
        $user = $this->userManager->get($uid);
        if ($user === null) {
            return new DataResponse(array('msg' => 'not found!'), Http::STATUS_NOT_FOUND);
        }

        $accounts = explode(',', $this->config->getAppValue('moodle', 'systemaccounts'));
        if (!in_array($uid, $accounts)) {
            $accounts[] = $uid;
        }
        $this->config->setAppValue('moodle', 'systemaccounts', implode(',', $accounts));
        return new DataResponse([
            'msg' => 'System account added successfully.'
        ]);
    }

    /**
     * @param string $uid
     * @return DataResponse
     */
    public function removeSystemAccount($uid) {
        $user = $this->userManager->get($uid);
        if ($user === null) {
            return new DataResponse(array('msg' => 'not found!'), Http::STATUS_NOT_FOUND);
        }

        $accounts = explode(',', $this->config->getAppValue('moodle', 'systemaccounts'));
        $newaccounts = array();
        // Keep all account except for the one to be dropped.
        foreach ($accounts as $account) {
            if ($account !== $uid) {
                $newaccounts[] = $account;
            } else {
                // Drop this one.
                continue;
            }
        }
        $this->config->setAppValue('moodle', 'systemaccounts', implode(',', $newaccounts));
        return new DataResponse([
            'msg' => 'System account removed successfully.'
        ]);
    }
}
