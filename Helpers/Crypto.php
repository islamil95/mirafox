<?php

class Crypto {
	// @todo: совсем нехорошо, потому что у класса слишком много областей отвественности.
	// Генерация пароля должна быть отдельно, и лучше - стратегией.
	const MIN_PASSWORD_LENGTH = 8;
	const MAX_PASSWORD_LENGTH = 10;

	const AMOUNT_LOW_ALPHABETIC = 4;
	const AMOUNT_UPPER_ALPHABETIC = 4;
	const AMOUNT_DIGITS = 4;

    /**
     * Генерация пароля
     * @return string
     */
    public static function genPassword() {

        $types = array(
            array(
                'symbols' => "abcdefghjkmnpqrstuwxyz",
                'amount' => static::AMOUNT_LOW_ALPHABETIC
            ),
            array(
                'symbols' => "ABCDEFGHJKMNPQRSTUWXYZ",
                'amount' => static::AMOUNT_UPPER_ALPHABETIC
            ),
            array(
                'symbols' => "23456789",
                'amount' => static::AMOUNT_DIGITS
            ),
        );

        $password = "";

        $requiredPasswordLength = random_int(static::MIN_PASSWORD_LENGTH, static::MAX_PASSWORD_LENGTH);

        $lastTypeIndex = count($types) - 1;

        foreach ($types as $typeIndex => $value) {
          // Из каждой группы случайные символы в случайном количестве.

          // Если это не последний остающийся тип, то кол-во его символов берем случайное
          if ($typeIndex < $lastTypeIndex) {
            $typeLength = random_int(1, $value['amount']);
          }
          // в противном случае кол-во символов - дополнение до длины пароля.
          else {
            $typeLength = $requiredPasswordLength - mb_strlen($password);
          }

          $lastSymbolsIndex = mb_strlen($value['symbols']) - 1;
          // Выбираем случайные символы до их кол-ва для этого типа.
          for ($i = 0; $i < $typeLength; $i++) {
            $symbolIndex = random_int(0, $lastSymbolsIndex);
            $password .= $value['symbols'][$symbolIndex];
          }
        }

		    // И дополнительно перемешиваем полученную строку.
        $passwordLength = mb_strlen($password);

        for ($i = 1; $i < $passwordLength; $i++) {
            $symbolIndex = random_int(0, $i);
            $tmp = $password[$i];
            $password[$i] = $password[$symbolIndex];
            $password[$symbolIndex] = $tmp;
        }

        return $password;
    }

    public static function encodeString($string){
        $string = strtolower($string);
        $xorString = Crypto::xorString($string);
        return base64_encode($xorString);
    }

    public static function decodeString($string){
        $deXorString = base64_decode($string);
        return Crypto::xorString($deXorString);
    }

    /**
     * Xor шифрование/дешифрование строки
     * @param string $string пароль
     * @return string
     */
    public static function xorString($string)
	{
        $key = App::config("crypto.xor_string_salt");
        $modulus = mb_strlen($key);
	$outText = "";
        for($i = 0; $i < mb_strlen($string); $i++)
            $outText .= $string{$i} ^ $key{$i % $modulus};

		return $outText;
    }

    /**
     * Хеширование пароля пользователя
     * @param string $password пароль
     * @return string
     */
    public static function hashUserPassword($password) {
        return md5(md5($password));
    }

}
