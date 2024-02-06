<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParserController extends Controller
{
    private $jsonData;
    private $data;

    public function __construct()
    {
        $this->jsonData = file_get_contents('assets/json/carsValid.json');
        $this->data = json_decode($this->jsonData, true);
    }

    public function checkBrand($brand)
    {
        foreach ($this->data as $car) {
            if ($car['name'] === $brand) {
                return true;
            }
        }
        return false;
    }

    public function checkModel($model)
    {
        foreach ($this->data as $brand) {
            foreach ($brand['models'] as $carModel) {
                if ($carModel === $model || $carModel === '(все модели)') {
                    return true;
                }
            }
        }
        return false;
    }

    public function parseBrand($value)
    {
        $brand = $value;
        $brandExists = $this->checkBrand($brand);
        return $brandExists;
    }

    public function parseModel($value)
    {
        $model = $value;
        $modelExists = $this->checkModel($model);
        return $modelExists;
    }

    public function parseCity($value)
    {
        $city = $value;
        $cityExists = $this->checkCity($city);
        return $cityExists;
    }

    public function checkCity($city)
    {
        $jsonData = file_get_contents('assets/json/cityValid.json');
        $data = json_decode($jsonData, true);
        foreach ($data as $cityData) {
            if ($cityData['city_ru'] === $city) {
                return true;
            }
        }
        return false;
    }

    public function optimizeFiles()
    {
        $carsData = $this->optimizeCarsFile();
        $cityData = $this->optimizeCityFile();

        $carsJson = json_encode($carsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents('assets/json/carsValid.json', $carsJson);

        $cityJson = json_encode($cityData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents('assets/json/cityValid.json', $cityJson);

        return "Файлы оптимизированы и сохранены как carsValid.json и cityValid.json";
    }

    private function optimizeCarsFile()
    {
        $jsonData = file_get_contents('assets/json/cars.json');
        $data = json_decode($jsonData, true);

        $optimizedData = [];
        foreach ($data as $brand) {
            $optimizedBrand = [
                'name' => $brand['name'],
                'models' => []
            ];

            foreach ($brand['models'] as $model) {
                $optimizedBrand['models'][] = $model['name'];
            }

            $optimizedData[] = $optimizedBrand;
        }

        return $optimizedData;
    }

    private function optimizeCityFile()
    {
        $jsonData = file_get_contents('assets/json/city_russia.json');
        $data = json_decode($jsonData, true);

        $optimizedData = [];
        foreach ($data as $city) {
            $optimizedCity = [
                'city_ru' => $city['city_ru'],
            ];
            $optimizedData[] = $optimizedCity;
        }

        return $optimizedData;
    }
}
