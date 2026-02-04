<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = Transaction::with(['user', 'category'])
            ->where('user_id', $request->user()->id)
            ->latest('date')
            ->get();

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['user_id'] = $request->user()->id;

        $transaction = Transaction::create($data)
            ->load(['user', 'category']);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Transaction $transaction)
    {
        $this->ensureOwnership($transaction, $request);

        return new TransactionResource(
            $transaction->load(['user', 'category'])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->ensureOwnership($transaction, $request);

        $transaction->update($this->validatedData($request));

        return new TransactionResource(
            $transaction->load(['user', 'category'])
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Transaction $transaction): JsonResponse
    {
        $this->ensureOwnership($transaction, $request);

        $transaction->delete();

        return response()->json(null, 204);
    }

    public function summary(Request $request)
    {
        $userId = $request->user()->id;

        $income = Transaction::where('user_id', $userId)
            ->whereHas('category', fn ($query) => $query->where('type', 'income'))
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->whereHas('category', fn ($query) => $query->where('type', 'expense'))
            ->sum('amount');

        $data = [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'net' => (float) ($income - $expense),
        ];

        return $this->successResponse($data, 'Summary generated');
    }

    public function byCategory(Request $request)
    {
        $userId = $request->user()->id;

        $totals = Transaction::with('category')
            ->where('user_id', $userId)
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'category_id' => $category?->id,
                    'category_name' => $category?->name,
                    'category_type' => $category?->type,
                    'total_amount' => (float) $items->sum('amount'),
                ];
            })
            ->values();

        return $this->successResponse($totals, 'Totals grouped by category');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric'],
            'note' => ['nullable', 'string'],
            'date' => ['required', 'date'],
        ]);
    }

    protected function ensureOwnership(Transaction $transaction, Request $request): void
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to access this transaction.');
        }
    }
}
