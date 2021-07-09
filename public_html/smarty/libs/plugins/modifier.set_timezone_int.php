<?php

function smarty_modifier_set_timezone_int($time, $timezone) {
    return Timezone::setTimezoneInt($time, $timezone);
}