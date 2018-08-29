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

use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;

class CheckSetupController extends Controller {
	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

    /**
     * @return DataResponse
     */
    public function checkSupportsBearerToken() {
        if (isset($_SERVER['HTTP_AUTHENTICATION']) &&
            $_SERVER['HTTP_AUTHENTICATION'] === 'Bearer xyz') {
            return new DataResponse([
                'supportsBearerToken' => true,
                ]);
        }
        // Bearer authentication token was not transmitted.
        return new DataResponse([
                'supportsBearerToken' => false,
                ]);
    }

}
