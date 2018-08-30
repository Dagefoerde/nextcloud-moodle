<!--
  - @copyright 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
  -
  - @author Jan Dageförde <jan.dagefoerde@uni-muenster.de>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->
<template>
    <tr>
        <td class="avatar">
            <img alt="" width="32" height="32" :src="generateAvatar(id, avatar_version, 32)"
                 :srcset="generateAvatar(id, avatar_version, 64)+' 2x, '+generateAvatar(id, avatar_version, 128)+' 4x'">
        </td>
        <th class="name" scope="row">{{id}}</th>

        <td class="quota">
            <div :class="quotaClass" :title="quotaTitle">
                <a href="#" class="nav-icon-quota svg">
                    <p id="quotatext">{{quotaText}}</p>
                    <div class="quota-container">
                        <progress :value="usage_relative" max="100" :class="quotaWarn"></progress>
                    </div>
                </a>
            </div>
        </td>
        <td class="action-column"><span><a class="icon-delete has-tooltip" :title="t('moodle', 'Remove from list')" @click="$emit('delete', id)"></a></span></td>
    </tr>
</template>

<script>
export default {
	name: 'SystemAccountItem',
	props: {
		account: {
			type: Object,
			required: true
		}
	},
	data: function() {
			return {
				id: this.account.id,
                quota: this.account.quota,
                usage: this.account.usage,
                total_space: this.account.total_space,
                usage_relative: this.account.usage_relative,
                avatar_version: this.account.avatar_version,
			};
	},
	computed: {
        quotaWarn: function() {
            if (this.usage_relative > 80) {
                return "warn";
            }
            return ""
        },
        quotaText: function () {
            if (this.quota !== -3) { // -3 is \OCP\Files\FileInfo::SPACE_UNLIMITED.
                return t('moodle', '{usage} of {total_space} used', this)
            }
            return t('moodle', '{usage} used', this)
        },
        quotaClass: function () {
            if (this.quota !== -3) {
                return 'has-tooltip';
            }
            return '';
        },
        quotaTitle: function () {
            if (this.quota !== -3) {
                return this.usage_relative + '';
            }
            return '';
        },
	},
	methods: {

        /**
         * Generate avatar url
         *
         * @param {string} user The user name
         * @param {int} version User's avatar version
         * @param {int} size Size integer, default 32
         * @returns {string}
         */
        generateAvatar(user, version, size=32) {
            return OC.generateUrl(
                '/avatar/{user}/{size}?v={version}',
                {
                    user: user,
                    size: size,
                    version: version
                }
            );
        },
	}
}
</script>
