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
