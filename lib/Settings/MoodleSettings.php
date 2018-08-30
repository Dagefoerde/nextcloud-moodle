<?php
/**
 * @copyright 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
 * @author Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
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

namespace OCA\Moodle\Settings;

use OC_Helper;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Settings\ISettings;

class MoodleSettings implements ISettings {

	/** @var IConfig */
	private $config;
	/** @var IUserManager */
    private $userManager;

    /**
     * MoodleSettings constructor.
     *
     * @param IConfig $config
     * @param IUserManager $userManager
     */
	public function __construct(IConfig $config, IUserManager $userManager) {
		$this->config = $config;
		$this->userManager = $userManager;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		return new TemplateResponse('moodle', 'settings/index', [], '');
	}

	/**
	 * @return string
	 */
	public function getSection() {
		return 'moodle';
	}

	/**
	 * @return int
	 */
	public function getPriority() {
		return 0;
	}
}
