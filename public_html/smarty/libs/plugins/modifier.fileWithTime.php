<?php

/**
 * вывод измени файла вместе с временной меткой его изменения
 */
function smarty_modifier_fileWithTime($filePath) {
	return Helper::fileWithTime($filePath);
}
