<?php

namespace Spsr;

use \Spsr\Core as SpsrCore;

class Tariff extends SpsrCore {
    public $cities = [];

    public function __construct(array $config) {
        parent::__construct($config);
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * @param string $name
     * @param string $country
     * @param callable|bool $callback
     * @return array|bool
     */
    public function getCities($name = '', $country = '', $callback = false) {
        if (!$name || !$country)
            throw new \InvalidArgumentException('Wrong city arguments');

        $query = <<<XML
    <root   xmlns="http://spsr.ru/webapi/Info/GetCities/1.0">
        <p:Params Name="WAGetCities" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />
        <GetCities CityName="{$name}" CountryName="{$country}" />
    </root>
XML;

        $xmlCities = $this->makeXmlCall($query)->City;

        foreach ($xmlCities->Cities as $city)
            $this->cities[] = current($city->attributes());

        if (is_callable($callback)) {
            foreach ($this->cities as $c) {
                if ($callback($c) === true)
                    return $c;
            }
            return false;
        } else {
            return $this->cities;
        }
    }

    public function calculateTariff(array $opts) {
        return simplexml_load_string($this->makeGetCall(
            'http://spsr.ru/tarifcalc/?fn=TARIFFCOMPUTE_2&'.http_build_query(array_merge($opts, array('ICN' => $this->ICN, 'SID' => $this->SID)))
        ));
    }

}