<!--
  - @copyright Copyright (c) 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
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
    <div id="moodle-vue-app">
    <div id="moodle-settings" class="section">
        <h2 class="inlineblock">{{ t('moodle', 'Moodle Integration Settings') }}</h2>
        <p class="settings-hint">{{ t('moodle', 'Integrating Moodle with Nextcloud requires some configuration on the Nextcloud end. The following reports any configuration mistakes found.') }}</p>
        <div id="moodlechecks">
            <div class="loading" v-if="!checkLoaded"></div>
            <span v-else>
                <ul class="errors" v-if="this.errors.length > 0">
                    <li v-for="error in errors" v-html="error"></li>
                </ul>
                <ul class="allgood" v-if="this.errors.length === 0">
                    <li>
                        <span class="ui-icon icon-checkmark-color"></span>
                        {{ t('moodle', 'No issues found. The integration is ready to go.') }}
                    </li>
                </ul>
            </span>
        </div>
    </div>
    <div id="moodle-systemaccounts" class="section">

        <h2 class="inlineblock">{{ t('moodle', 'System Account') }}</h2>
        <p class="settings-hint">{{ t('moodle', 'For some features, Moodle requires a special, impersonal user in Nextcloud. Here you can define and monitor it.') }}</p>

        <table id="userlist" class="grid" v-if="accounts.length > 0">
            <thead>
            <tr>
                <th id="headerAvatar" scope="col"></th>
                <th id="headerName" scope="col">{{ t('moodle', 'Username') }}</th>
                <th id="headerQuota" scope="col">{{ t('moodle', 'Quota') }}</th>
                <th id="headerRemove" scope="col">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <SystemAccountItem v-for="account in accounts"
                       :key="account.id"
                       :account="account"
                       @delete="deleteAccount"
            />
             </tbody>
        </table>

        <h3>{{ t('moodle', 'Add account') }}</h3>
        <span v-if="newAccount.error" class="msg error">{{newAccount.errorMsg}}</span>
        <form id="add-systemaccount-form" @submit.prevent="addAccount">
            <input id="systemaccount-name" type="text" :placeholder="t('moodle', 'Username')" v-model="newAccount.name">
            <input type="submit" class="button" :value="t('moodle', 'Add as system account')">
        </form>
    </div>
    </div>
</template>

<script>
import axios from 'axios';
import SystemAccountItem from './components/SystemAccountItem';

export default {
	name: 'App',
	components: {
		SystemAccountItem
	},
	data: function() {
		return {
		    checkLoaded: false,
		    errors: [],
			accounts: [],
			newAccount: {
				name: '',
				errorMsg: '',
				error: false
			}
		};
	},
	beforeMount: function() {
		let requestToken = OC.requestToken;
		let tokenHeaders = { headers: { requesttoken: requestToken } };

		axios.get(OC.generateUrl('/apps/moodle/settings/systemaccounts'), tokenHeaders)
			.then((response) => {
			this.accounts = response.data;
		});
	},
    mounted: function() {
        let requestHeaders = { headers:
                {
                    requesttoken: OC.requestToken,
                    Authentication: 'Bearer xyz'
                } };

        axios.get(OC.generateUrl('/apps/moodle/settings/checksupportsbearertoken'), requestHeaders)
            .then((response) => {
                if (response.status === 200 && response.data) {
                    if (!response.data.supportsBearerToken) {
                        this.errors.push(
                            t('moodle', 'Bearer authentication token was not received. Likely, <code>mod_headers</code> is missing or misconfigured.')
                        );
                    }
                    if (!response.data.sharingEnabled) {
                        this.errors.push(
                            t('moodle', 'At <code>Administration &gt; Sharing</code>, allow users to share via link.')
                        );
                    }
                    if (!response.data.sharesNeverExpire) {
                        this.errors.push(
                            t('moodle', 'At <code>Administration &gt; Sharing</code>, disable the default expiration date.')
                            // TODO add: This is a workaround!
                        );
                    }
                    if (!response.data.shareExpirationNotEnforced) {
                        this.errors.push(
                            t('moodle', 'At <code>Administration &gt; Sharing</code>, disable enforcement of the expiration date.')
                        );
                    }
                    if (!response.data.validClients.length) {
                        this.errors.push(
                            t('moodle', 'You need to configure a valid OAuth 2 client for Moodle. Check that its redirection URI ends with <code>/admin/oauth2callback.php</code>.')
                        );
                    }
                } else {
                    this.errors.push(
                        t('core', 'Error occurred while checking server setup')
                    );
                }

                // Check whether access is via HTTPS.
                if(OC.getProtocol() !== 'https') {
                    this.errors.push(
                        t('moodle', 'The server must be accessed via HTTPS. For security reasons, Moodle refuses to connect to an unencrypted server.')
                    )
                }

                this.checkLoaded = true;

            });
    },
	methods: {
		deleteAccount(id) {
		    if (OC.PasswordConfirmation.requiresPasswordConfirmation()) {
		        OC.PasswordConfirmation.requirePasswordConfirmation(() => this.deleteAccount(id));
		        return;
            }

			let requestToken = OC.requestToken;
			let tokenHeaders = { headers: { requesttoken: requestToken } };

			axios.post(OC.generateUrl('/apps/moodle/settings/removesystemaccount'),
                {
                    uid: id
                },
                tokenHeaders)
				.then((response) => {
					this.accounts = this.accounts.filter(account => account.id !== id);
				});
		},
		addAccount() {
		    if (this.newAccount.name === '') {
                this.newAccount.error = true;
                this.newAccount.errorMsg = t('moodle', 'You need to specify an existing user');
                return;
            }

            if (OC.PasswordConfirmation.requiresPasswordConfirmation()) {
                OC.PasswordConfirmation.requirePasswordConfirmation(this.addAccount);
                return;
            }

			let requestToken = OC.requestToken;
			let tokenHeaders = { headers: { requesttoken: requestToken } };
			this.newAccount.error = false;

			axios.post(
                OC.generateUrl('/apps/moodle/settings/addsystemaccount'),
				{
					uid: this.newAccount.name
				},
				tokenHeaders
			).then(response => {
				this.accounts.push(response.data);

				this.newAccount.name = '';
			}).catch(reason => {
				this.newAccount.error = true;
				this.newAccount.errorMsg = reason.response.data.msg;
			});
		}
	},
}
</script>
