<?php

namespace Model;

/**
 * Description of Currency
 *
 * @author jsosnovsky
 */
class CurrencyModel {
    const RUB	    =	643;
    const USD	    =	840;
    
    /**
     * Идентификатор записи
     * 
     * @var int 
     */
    private $id;
    /**
     * Значение курса
     * 
     * @var double 
     */
    private $rate;
    
    /**
     * Дата курса
     * 
     * @var \DateTimeImmutable
     */
    private $date;
    
    /**
     * Идентификатор валюты
     * 
     * @var \Currency\CurrencyModel::RUB|\Currency\CurrencyModel::USD
     */
    private $currencyId;
    
    /**
     * Конструктор
     * 
     * @param double $rate Курс обмена
     * @param \DateTimeImmutable $date Дата курса
     * @param \Currency\CurrencyModel::RUB|\Currency\CurrencyModel::USD $currencyId Идентификатор валюты
     * @param int $id Идентификатор записи
     */
    function __construct($rate, $date, $currencyId, $id = 0) {
	if(!($date instanceof \DateTimeImmutable)){
	    throw new \Exception("Illegal type for date, should be a DateTimeImmutable");
	}
	$this->id = (int) $id;
	$this->rate = (double) $rate;
	$this->date = $date;
	$this->currencyId = (int) $currencyId;
    }
    
    /**
     * Возвращает значение обменного курса
     * 
     * @return double
     */
    public function getRate() {
	return $this->rate;
    }
    
    /**
     * Возвращает дату установления курса
     * 
     * @return \DateTimeImmutable
     */
    public function getDate() {
	return $this->date;
    }
    
    /**
     * Возвращает идентификатор валюты
     * 
     * @return \Currency\CurrencyModel::RUB|\Currency\CurrencyModel::USD
     */
    public function getCurrencyId() {
	return $this->currencyId;
    }
}
