/*
 * Copyright (c) 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

/**
 * Runs setup checks on the server side
 *
 * @return $.Deferred object resolved with an array of error messages
 */
$(document).ready(function() {

    var deferred = $.Deferred();
    var afterCall = function (data, statusText, xhr) {
        var messages = [];
        if (xhr.status === 200 && data) {
            if (!data.serverHasInternetConnection) {
                messages.push({
                    msg: t('core', 'This server has no working Internet connection: Multiple endpoints could not be reached. This means that some of the features like mounting external storage, notifications about updates or installation of third-party apps will not work. Accessing files remotely and sending of notification emails might not work, either. Establish a connection from this server to the Internet to enjoy all features.'),
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (!data.isMemcacheConfigured) {
                messages.push({
                    msg: t('core', 'No memory cache has been configured. To enhance performance, please configure a memcache, if available. Further information can be found in the <a target="_blank" rel="noreferrer noopener" href="{docLink}">documentation</a>.', {docLink: data.memcacheDocs}),
                    type: OC.SetupChecks.MESSAGE_TYPE_INFO
                });
            }
            if (!data.isUrandomAvailable) {
                messages.push({
                    msg: t('core', '/dev/urandom is not readable by PHP which is highly discouraged for security reasons. Further information can be found in the <a target="_blank" rel="noreferrer noopener" href="{docLink}">documentation</a>.', {docLink: data.securityDocs}),
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (data.isUsedTlsLibOutdated) {
                messages.push({
                    msg: data.isUsedTlsLibOutdated,
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (data.phpSupported && data.phpSupported.eol) {
                messages.push({
                    msg: t('core', 'You are currently running PHP {version}. Upgrade your PHP version to take advantage of <a target="_blank" rel="noreferrer noopener" href="{phpLink}">performance and security updates provided by the PHP Group</a> as soon as your distribution supports it.', {
                        version: data.phpSupported.version,
                        phpLink: 'https://secure.php.net/supported-versions.php'
                    }),
                    type: OC.SetupChecks.MESSAGE_TYPE_INFO
                });
            }
            if (data.phpSupported && data.phpSupported.version.substr(0, 3) === '5.6') {
                messages.push({
                    msg: t('core', 'You are currently running PHP 5.6. The current major version of Nextcloud is the last that is supported on PHP 5.6. It is recommended to upgrade the PHP version to 7.0+ to be able to upgrade to Nextcloud 14.'),
                    type: OC.SetupChecks.MESSAGE_TYPE_INFO
                });
            }
            if (!data.forwardedForHeadersWorking) {
                messages.push({
                    msg: t('core', 'The reverse proxy header configuration is incorrect, or you are accessing Nextcloud from a trusted proxy. If not, this is a security issue and can allow an attacker to spoof their IP address as visible to the Nextcloud. Further information can be found in the <a target="_blank" rel="noreferrer noopener" href="{docLink}">documentation</a>.', {docLink: data.reverseProxyDocs}),
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (!data.isCorrectMemcachedPHPModuleInstalled) {
                messages.push({
                    msg: t('core', 'Memcached is configured as distributed cache, but the wrong PHP module "memcache" is installed. \\OC\\Memcache\\Memcached only supports "memcached" and not "memcache". See the <a target="_blank" rel="noreferrer noopener" href="{wikiLink}">memcached wiki about both modules</a>.', {wikiLink: 'https://code.google.com/p/memcached/wiki/PHPClientComparison'}),
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (!data.hasPassedCodeIntegrityCheck) {
                messages.push({
                    msg: t(
                        'core',
                        'Some files have not passed the integrity check. Further information on how to resolve this issue can be found in the <a target="_blank" rel="noreferrer noopener" href="{docLink}">documentation</a>. (<a href="{codeIntegrityDownloadEndpoint}">List of invalid files…</a> / <a href="{rescanEndpoint}">Rescan…</a>)',
                        {
                            docLink: data.codeIntegrityCheckerDocumentation,
                            codeIntegrityDownloadEndpoint: OC.generateUrl('/settings/integrity/failed'),
                            rescanEndpoint: OC.generateUrl('/settings/integrity/rescan?requesttoken={requesttoken}', {'requesttoken': OC.requestToken})
                        }
                    ),
                    type: OC.SetupChecks.MESSAGE_TYPE_ERROR
                });
            }
            if (!data.isOpcacheProperlySetup) {
                messages.push({
                    msg: t(
                        'core',
                        'The PHP OPcache is not properly configured. <a target="_blank" rel="noreferrer noopener" href="{docLink}">For better performance it is recommended</a> to use the following settings in the <code>php.ini</code>:',
                        {
                            docLink: data.phpOpcacheDocumentation,
                        }
                    ) + "<pre><code>opcache.enable=1\nopcache.enable_cli=1\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=10000\nopcache.memory_consumption=128\nopcache.save_comments=1\nopcache.revalidate_freq=1</code></pre>",
                    type: OC.SetupChecks.MESSAGE_TYPE_INFO
                });
            }
            if (!data.isSettimelimitAvailable) {
                messages.push({
                    msg: t(
                        'core',
                        'The PHP function "set_time_limit" is not available. This could result in scripts being halted mid-execution, breaking your installation. Enabling this function is strongly recommended.'),
                    type: OC.SetupChecks.MESSAGE_TYPE_WARNING
                });
            }
            if (!data.hasFreeTypeSupport) {
                messages.push({
                    msg: t(
                        'core',
                        'Your PHP does not have freetype support. This will result in broken profile pictures and settings interface.'
                    ),
                    type: OC.SetupChecks.MESSAGE_TYPE_INFO
                })
            }
        } else {
            messages.push({
                msg: t('core', 'Error occurred while checking server setup'),
                type: OC.SetupChecks.MESSAGE_TYPE_ERROR
            });
        }
        deferred.resolve(messages);
    };

    $.ajax({
        type: 'GET',
        headers: {
            'Authentication': 'Bearer xyz',
        },
        url: OC.generateUrl('/apps/moodle/settings/checksupportsbearertoken'),
        allowAuthErrors: true
    }).then(afterCall, afterCall);

});