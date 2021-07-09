<?php

function smarty_modifier_packageName($type) {
    return PackageManager::getName($type);
}
