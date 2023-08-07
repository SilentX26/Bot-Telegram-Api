<?php
namespace App\Library;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpoonApi {
    private $API_URL = 'https://api.spoonacular.com/';
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    private function parseResult(Response $response)
    {
        return $response->status() !== 200 ? false : $response->json();
    }
 
    private function buildParams(array $params):  array
    {
        return array_merge($params, ['apiKey' => $this->apiKey]);
    }

    public static function getRecipeImageUrl($id):string
    {
        return 'https://spoonacular.com/recipeImages/' . $id . '-312x231.jpg';
    }

    public function searchRecipe(array $params = []): mixed
    {
        $params = $this->buildParams($params);
        $search = Http::get($this->API_URL . 'recipes/complexSearch', $params);

        return $this->parseResult($search);
    }

    public function detailRecipe($id): mixed 
    {
        $params = $this->buildParams([ 'includeNutrition' => true ]);
        $detail = Http::get($this->API_URL . 'recipes/' . $id . '/information', $params);

        return $this->parseResult($detail);
    }
    
    public function randomRecipe(): mixed 
    {
        $params = $this->buildParams([ 'number' => 5 ]);
        $random = Http::get($this->API_URL . 'recipes/random', $params);

        return $this->parseResult($random);
    }
}