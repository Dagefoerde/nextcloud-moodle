/*
 * Copyright (c) 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

/*    if (OC.PasswordConfirmation.requiresPasswordConfirmation()) {
        OC.PasswordConfirmation.requirePasswordConfirmation(removeSystemAccount.bind(this));
        return;
    }
*/


/**
 * Runs setup checks on the server side
 *
 */
$(document).ready(function() {
    var $el = $('#moodlechecks');
    $el.find('.loading').addClass('hidden');
    var deferred = $.Deferred();
    var afterCall = function (data, statusText, xhr) {
        var messages = [];
        if (xhr.status === 200 && data) {
            if (!data.supportsBearerToken) {
                messages.push({
                    msg: t('moodle', 'Bearer authentication token was not received. Likely, <code>mod_headers</code> is missing or misconfigured.'),
                    type: 'error'
                });
            }
        } else {
            messages.push({
                msg: t('core', 'Error occurred while checking server setup'),
                type: 'error'
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

    $.when(deferred.promise()).then(function(messages) {
        $el.find('.loading').addClass('hidden');

        var hasMessages = false;
        var $errorsEl = $el.find('.errors');
        var $warningsEl = $el.find('.warnings');
        var $infoEl = $el.find('.info');

        for (var i = 0; i < messages.length; i++) {
            switch (messages[i].type) {
                case 'info':
                    $infoEl.append('<li>' + messages[i].msg + '</li>');
                    break;
                case 'warning':
                    $warningsEl.append('<li>' + messages[i].msg + '</li>');
                    break;
                case 'error':
                default:
                    $errorsEl.append('<li>' + messages[i].msg + '</li>');
            }
        }

        var errorsCount = $errorsEl.find('li').length;
        var warningsCount = $warningsEl.find('li').length;
        var infoCount = $infoEl.find('li').length;
        if (errorsCount > 0) {
            $errorsEl.removeClass('hidden');
            hasMessages = true;
        }
        if (warningsCount > 0) {
            $warningsEl.removeClass('hidden');
            hasMessages = true;
        }
        if (infoCount > 0) {
            $infoEl.removeClass('hidden');
            hasMessages = true;
        }
        if (errorsCount + warningsCount + infoCount === 0) {
            $el.find('.allgood').removeClass('hidden');
        }
    });

});