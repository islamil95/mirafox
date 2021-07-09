<?php

function smarty_modifier_set_timezone($datetime, $timezone) {
    return Timezone::setTimezone($datetime, $timezone);
}