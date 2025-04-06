<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\SearchRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Services\SearchRecordService;
use App\Exceptions\ApiServiceException;
use App\Contracts\SearchServiceInterface;
use App\Contracts\SearchRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\NotificationServiceInterface;
use App\Exceptions\EloquentSearchSaveException;
use Illuminate\Pagination\LengthAwarePaginator;



class SearchController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected SearchServiceInterface $searchService, protected SearchRepositoryInterface $searchRepository, protected NotificationServiceInterface $notificationService, protected SearchRecordService $recordService) {}

    public function index()
    {
        session()->forget('current_search_id');
        $randomJoke = $this->searchService->getRandomChuckNorrisJoke();

        return view('search.index', ['randomJoke' => $randomJoke['value'] ?? null]);
    }

    public function search(SearchRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $type = $validatedData['type'];
            $query = $validatedData['query'] ?? null;
            $page = $request->get('page', 1);
            $perPage = 10;
            $email = $validatedData['email'] ?? null;

            $allResults = $this->searchService->getAllResults($validatedData);
         

            // Extract the raw items from the LengthAwarePaginator if it's an object
            $resultsToRecord = is_object($allResults) && method_exists($allResults, 'items')
                ? $allResults->items()
                : $allResults;
               // dd($allResults);
            $this->recordService->handleSearchRecord($type, $query, $resultsToRecord, $email); // Call service to handle saving

            $paginatedResults = $this->paginateResults($request, $allResults, $page, $perPage);

            $this->notificationService->handleSearchResultNotification($request, collect($allResults)->take(config('mail.send_amount_results'))->toArray(), $type, $query, $email, count($allResults)); // Call service for notification

            return $this->prepareResponse($request, $paginatedResults, $query, $type);
        } catch (ApiServiceException $e) {
            return $this->errorResponseFront('Error al conectar con la API', $e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (EloquentSearchSaveException $e) {
            return $this->errorResponseFront('Error al guardar los resultados de la búsqueda', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    protected function paginateResults(SearchRequest $request, array $allResults, int $page, int $perPage): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($allResults, $offset, $perPage, true);
        return new LengthAwarePaginator(
            $itemsForCurrentPage,
            count($allResults),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
    protected function prepareResponse(SearchRequest $request, LengthAwarePaginator $paginatedResults, ?string $query, string $type)
    {
        if ($request->wantsJson()) {
            return response()->json($paginatedResults);
        }
        return view('search.results', ['results' => $paginatedResults, 'searchTerm' => $query, 'searchType' => $type]);
    }
    public function categories()
    {
        try {
            $categories = $this->searchService->getCategories();
            return response()->json($categories);
        } catch (ApiServiceException $e) {
            return $this->errorResponseJson('Error al obtener las categorías', $e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Exception $e) {
            return $this->errorResponseJson('Error al obtener las categorías', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
