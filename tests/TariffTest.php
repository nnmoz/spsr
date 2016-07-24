<?php

use Spsr\Tariff;

class Tariffest extends PHPUnit_Framework_TestCase {
    public $tariff;

    public function setUp() {
        $this->tariff = new Tariff(array('env' => 'test'));
    }

    public function testCity() {
        $city = $this->tariff->getCities('ново', 'россия', function ($el) {
            if ($el['CityName'] == 'Новоалександрово') return true;
        });
        $this->assertEquals("1196908207", $city["City_ID"]);
    }

    public function testTarrif() {
        $tariff = $this->tariff->calculateTariff(array(
            'ToCity' => '138|0',
            'FromCity' => '992|0',
            'Weight' => 0.025,
            'PlatType' => 1,
        ));

    }
}