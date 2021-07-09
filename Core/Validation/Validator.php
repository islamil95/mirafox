<?php

namespace Core\Validation;

use \Illuminate\Translation\FileLoader;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Translation\Translator;
use \Illuminate\Validation\Factory;
use \Illuminate\Support\MessageBag;
use \Illuminate\Validation\DatabasePresenceVerifier;
use \Translations;

class Validator {

    /**
     * Доступный глобально объект валидатора (полностью настроенный и готовый к работе)
     * @var \Illuminate\Validation\Validator
     */
    protected static $instance;	

    /**
     * Создать и настроить объект валидатора
     * @param \Illuminate\Database\Capsule\Manager $capsule
     * @return void
     */
    public static function boot($capsule) {
        $loader = new FileLoader(new Filesystem, $_SERVER["DOCUMENT_ROOT"] . "/../languages");
        $translator = new Translator($loader, Translations::getLang());
        $validator = new Factory($translator);
        $verifier = new DatabasePresenceVerifier($capsule->getDatabaseManager());
        $validator->setPresenceVerifier($verifier);
    	static::$instance = $validator;
    }

    /**
     * Быстрый доступ к методу make нашего глобального валидатора ($instance)
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public static function make(array $data, array $rules, array $messages = [], array $customAttributes = []) {
        return static::$instance->make($data, $rules, $messages, $customAttributes);
    }

}
