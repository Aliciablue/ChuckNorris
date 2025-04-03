<?php

namespace Database\Factories;

use App\Models\Search;
use App\Services\SearchService;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Search>
 */
class SearchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Search::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['keyword', 'category', 'random'];
        $type = $this->faker->randomElement($types);
        $query = null;

        if ($type === 'keyword') {
            $query = $this->faker->word();
        } elseif ($type === 'category') {
            // Resolve an instance of SearchService from the container
            $searchService = App::make(SearchService::class);
            $categories = $searchService->getCategories();

            if (!empty($categories)) {
                $query = $this->faker->randomElement($categories);
            } else {
                $query = $this->faker->word(); // Fallback si no hay categorías en caché
            }
        }

        return [
            'type' => $type,
            'query' => $query,
            'results' => json_encode([['value' => $this->faker->sentence()]]),
            'email' => $this->faker->safeEmail(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
