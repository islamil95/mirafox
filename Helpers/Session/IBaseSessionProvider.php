<?php

namespace Session;

interface IBaseSessionProvider {
	public function get($id, $defaultValue = null);
	public function set($id, $value);
	public function isExist($id):bool;
	public function notExist($id):bool;
	public function notEmpty($id):bool;
	public function isEmpty($id):bool;
	public function delete($id):bool;
	public function getSessionId():string;
	public function validateSessionId():bool;
}
