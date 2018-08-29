/*
 * Copyright (c) 2018, Jan Dageförde <jan.dagefoerde@uni-muenster.de>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

var addSystemAccount = function () {
    if ($('#moodle-systemaccounts #systemaccount-name').val() === '') {
        return;
    }

    if (OC.PasswordConfirmation.requiresPasswordConfirmation()) {
        OC.PasswordConfirmation.requirePasswordConfirmation(addSystemAccount);
        return;
    }

    var _this = this;
    //this._toggleAddingToken(true);

    var accountID = $('#moodle-systemaccounts #systemaccount-name').val();
    var systemaccount = $.ajax(OC.generateUrl('/apps/moodle/settings/addsystemaccount'), {
        method: 'POST',
        data: {
            uid: accountID
        }
    });

    $.when(systemaccount).done(function (resp) {
        document.location.reload();
    });
    $.when(systemaccount).fail(function () {
        OC.Notification.showTemporary(t('core', 'Error while adding system account'));
    });
    $.when(systemaccount).always(function () {
        //_this._toggleAddingToken(false);
    });
}

var removeSystemAccount = function (e) {
    if (OC.PasswordConfirmation.requiresPasswordConfirmation()) {
        OC.PasswordConfirmation.requirePasswordConfirmation(removeSystemAccount.bind(this));
        return;
    }

    var userId = $(this).data().userid;

    var systemaccount = $.ajax(OC.generateUrl('/apps/moodle/settings/removesystemaccount'), {
        method: 'POST',
        data: {
            uid: userId
        }
    });

    $.when(systemaccount).done(function (resp) {
        document.location.reload();
    });
    $.when(systemaccount).fail(function () {
        OC.Notification.showTemporary(t('core', 'Error while adding system account'));
    });
}


/**
 * Runs setup checks on the server side
 *
 */
$(document).ready(function() {
    var addAppPasswordBtn = $('#add-systemaccount');
    addAppPasswordBtn.click(addSystemAccount);
    var appPasswordName = $('#systemaccount-name');
    appPasswordName.on('keypress', function(event) {
        if (event.which === 13) {
            addSystemAccount();
        }
    });
    var removeEntryButtons = $('#moodle-systemaccounts input.remove');
    removeEntryButtons.click(removeSystemAccount);

    var $el = $('#moodlechecks');
    $el.find('.loading').addClass('hidden');
    var deferred = $.Deferred();
    var afterCall = function (data, statusText, xhr) {
        var messages = [];
        if (xhr.status === 200 && data) {
            if (!data.supportsBearerToken) {
                messages.push({
                    msg: t('moodle', 'Bearer authentication token was not received. Likely, mod_headers is missing or misconfigured.'),
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