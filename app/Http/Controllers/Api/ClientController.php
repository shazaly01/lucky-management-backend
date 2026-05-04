<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\Api\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Client::class);

        $clients = Client::latest()->paginate(15);

        return ClientResource::collection($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): ClientResource
    {
        Gate::authorize('create', Client::class);

        $client = $this->clientService->createClient($request->validated());

        return new ClientResource($client);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): ClientResource
    {
        Gate::authorize('view', $client);

        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        Gate::authorize('update', $client);

        $updatedClient = $this->clientService->updateClient($client, $request->validated());

        return new ClientResource($updatedClient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        Gate::authorize('delete', $client);

        $this->clientService->deleteClient($client);

        return response()->json(['message' => 'تم حذف العميل بنجاح.']);
    }
}
