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

use OC_Helper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\IUser;
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
        if (in_array($uid, $accounts)) {
            return new DataResponse(array('msg' => 'already a system account!'), Http::STATUS_NOT_FOUND);
        }
        $accounts[] = $uid;
        $this->config->setAppValue('moodle', 'systemaccounts', implode(',', $accounts));
        return new DataResponse($this->formatAccount($user));
    }

    /**
     * @param string $uid
     * @return DataResponse
     */
    public function removeSystemAccount($uid) {
        $accounts = explode(',', $this->config->getAppValue('moodle', 'systemaccounts'));
        $newaccounts = array();
        // Keep all account except for the one to be dropped.
        foreach ($accounts as $account) {
            if (empty($account)) continue;
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

    /**
     * @return DataResponse
     */
    public function getSystemAccounts() {
        $setting = $this->config->getAppValue('moodle', 'systemaccounts');
        if (empty($setting)) {
            return new DataResponse([]);
        }

        $accounts = explode(',', $setting);
        $formattedAccounts = array();
        foreach ($accounts as $uid) {
            if (empty($uid)) continue;

            /** @var IUser $user */
            $user = $this->userManager->get($uid);
            if ($user === null) {
                // TODO Issue a warning that the system account seems to have been deleted.
                continue;
            }

            $formattedAccounts[] = $this->formatAccount($user);
        }

        return new DataResponse($formattedAccounts);
    }

    protected function formatAccount(IUser $user) {
        $storageInfo = $this->getStorageInfo($user->getUID());

        $formattedUser = array();
        // User metadata.
        $formattedUser['id'] = $user->getUID();
        $formattedUser['userAvatarVersion'] = $this->config->getUserValue(\OC_User::getUser(), 'avatar', 'version', 0);

        // Storage usage.
        $formattedUser['usage'] = \OC_Helper::humanFileSize($storageInfo['used']);
        if ($user->getQuota() === \OCP\Files\FileInfo::SPACE_UNLIMITED) {
            $totalSpace = $this->l10n->t('Unlimited');
        } else {
            $totalSpace = \OC_Helper::humanFileSize($storageInfo['total']);
        }
        $formattedUser['total_space'] = $totalSpace;
        $formattedUser['quota'] = $storageInfo['quota'];
        $formattedUser['usage_relative'] = $storageInfo['relative'];

        return $formattedUser;
    }

    /**
     * @param string $userId
     * @return array
     */
    protected function getStorageInfo($userId) {
        try {
            \OC_Util::tearDownFS();
            \OC_Util::setupFS($userId);
            $storage = OC_Helper::getStorageInfo('/');
            $data = [
                'free' => $storage['free'],
                'used' => $storage['used'],
                'total' => $storage['total'],
                'relative' => $storage['relative'],
                'quota' => $storage['quota'],
            ];
            \OC_Util::tearDownFS();
        } catch (NotFoundException $ex) {
            $data = [];
        }
        return $data;
    }

}
