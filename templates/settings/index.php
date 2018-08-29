<?php
script('moodle', 'checks');
style('moodle', 'style');
?>
<div id="moodle-settings" class="section">
    <h2 class="inlineblock"><?php p($l->t('Moodle Integration')); ?></h2>
    <p class="settings-hint"><?php p($l->t('Integrating Moodle with Nextcloud requires some configuration on the Nextcloud end. The following reports any configuration mistakes found.'));?></p>
    <div id="moodlechecks">
        <div class="loading"></div>
        <ul class="errors hidden"></ul>
        <ul class="warnings hidden"></ul>
        <ul class="info hidden"></ul>
        <ul class="allgood hidden">
            <li>
                <span class="ui-icon icon-checkmark-color"></span>
                <?php p($l->t('No issues found. The integration is ready to go.'));?>
            </li>
        </ul>
    </div>
</div>
<div id="moodle-systemaccounts" class="section">

    <h2 class="inlineblock"><?php p($l->t('System Account')); ?></h2>
    <p class="settings-hint"><?php p($l->t( 'For some features, Moodle requires a special, impersonal user in Nextcloud. Here you can define and monitor it.')); ?></p>


    <div id="app-password-form">
        <input id="systemaccount-name" type="text" placeholder="<?php p($l->t('Username')); ?>">
        <button id="add-systemaccount" class="button"><?php p($l->t('Add as system account')); ?></button>
    </div>

    <table id="userlist" class="grid" data-groups="<?php p($_['allGroups']);?>">
        <thead>
        <tr>
            <th id="headerAvatar" scope="col"></th>
            <th id="headerName" scope="col"><?php p($l->t('Username'))?></th>
            <th id="headerQuota" scope="col"><?php p($l->t('Quota')); ?></th>
            <th id="headerRemove" scope="col"><?php p($l->t('Remove from list')); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($_['accounts'] as $account) { ?>
            <tr>
                <td class="avatar">
                    <div class="avatardiv">
                    <img alt="" width="32" height="32"
                        src="<?php p(\OC::$server->getURLGenerator()->linkToRoute('core.avatar.getAvatar', ['userId' => $account['id'], 'size' => 32, 'v' => $account['userAvatarVersion']]));?>"
                        srcset="<?php
                        p(\OC::$server->getURLGenerator()->linkToRoute('core.avatar.getAvatar', ['userId' => $account['id'], 'size' => 64, 'v' => $account['userAvatarVersion']]));?> 2x, <?php
                        p(\OC::$server->getURLGenerator()->linkToRoute('core.avatar.getAvatar', ['userId' => $account['id'], 'size' => 128, 'v' => $account['userAvatarVersion']]));?> 4x"
                    >
                    </div>
                </td>
                <th class="name" scope="row"><?php p($account['id']); ?></th>
                <td class="quota">
                    <div id="quota-<?php p($account['id']); ?>" class="<?php
                    if ($account['quota'] !== \OCP\Files\FileInfo::SPACE_UNLIMITED) {
                    ?>has-tooltip" title="<?php p($account['usage_relative'] . '%');
                    } ?>">
                        <a href="#" class="nav-icon-quota svg">
                            <p id="quotatext"><?php
                                if ($account['quota'] !== \OCP\Files\FileInfo::SPACE_UNLIMITED) {
                                    p($l->t('%s of %s used', [$account['usage'], $account['total_space']]));
                                } else {
                                    p($l->t('%s used', [$account['usage']]));
                                } ?></p>
                            <div class="quota-container">
                                <progress value="<?php p($account['usage_relative']); ?>" max="100"
                                    <?php if($account['usage_relative'] > 80): ?> class="warn" <?php endif; ?>></progress>
                            </div>
                        </a>
                    </div>
                </td>
                <td class="remove">
                    <input class="remove" type="submit" data-userid="<?php p($account['id']); ?>" value="<?php p($l->t('Remove from list')); ?>">
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
