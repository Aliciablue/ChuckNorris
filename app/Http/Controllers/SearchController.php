<?php

namespace App\Http\Controllers;

use App\Contracts\NotificationServiceInterface;
use App\Models\Search;
use App\Jobs\SaveSearchJob;
use App\Http\Requests\SearchRequest;
use App\Jobs\SendSearchResultsEmail;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ApiServiceException;
use Illuminate\Support\Facades\Session;
use App\Contracts\SearchServiceInterface;
use App\Contracts\SearchRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\EloquentSearchSaveException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;


class SearchController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected SearchServiceInterface $searchService, protected SearchRepositoryInterface $searchRepository, protected NotificationServiceInterface $notificationService) {}

    public function index()
    {
        session()->forget('current_search_id');
        return view('search.index');
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

            $allResults = $this->searchService->getAllResults($validatedData, $type, $query, $email);

            $this->handleDatabaseSaving($type, $query, $allResults, $email);

            $paginatedResults = $this->paginateResults($request, $allResults, $page, $perPage);

            $this->handleNotificationSending($request, $allResults, $type, $query, $email);

            return $this->prepareResponse($request, $paginatedResults, $query, $type);
        } catch (ApiServiceException $e) {
            return $this->errorResponseFront('Error al conectar con la API', $e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (EloquentSearchSaveException $e) {
            return $this->errorResponseFront('Error al guardar los resultados de la búsqueda', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } 
    }

    protected function handleDatabaseSaving(string $type, ?string $query, array $allResults, ?string $email): void
    {
        // The first time we arrive to the page we set the session and save to database         
        if (!session()->get('current_search_id')) {
            SaveSearchJob::dispatch($this->searchRepository,$type, $query, $allResults, $email);
            session()->put('current_search_id', uniqid());
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
    protected function handleNotificationSending(SearchRequest $request, array $allResults, string $type, ?string $query, ?string $email): void
    {
        if ($request->has('email') && filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $allResultsUrl = route('search.results', ['type' => $type, 'query' => $query, 'locale' => app()->getLocale()]);
            $this->notificationService->sendSearchResultsNotification(collect($allResults), $type, $query, $email, $allResultsUrl, app()->getLocale());
            session()->flash('success', __('messages.email_sent_confirmation'));
        }
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
