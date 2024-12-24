<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanCreateRequest;
use App\Http\Requests\LoanSearchRequest;
use App\Http\Resources\LoanCollection;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoanManagementController extends Controller
{
    /**
     * Return a Loan instance or throw a 404 exception.
     *
     * @param int $idLoan
     * @return Loan
     * @throws HttpResponseException
     */
    private function getLoan(int $idLoan): Loan
    {
        $loan = Loan::with('member', 'librarian')->find($idLoan);
        if (!$loan) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ])->setStatusCode(404));
        }
        return $loan;
    }

    /**
     * Creates a new loan.
     *
     * @param LoanCreateRequest $request
     * @return JsonResponse A JSON response containing the new loan in JSON format.
     */
    public function create(LoanCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $loan = new Loan($data);
        $loan->save();
        return (new LoanResource($loan))->response()->setStatusCode(201);
    }

    /**
     * Retrieves a list of loans.
     *
     * @return JsonResponse A JSON response containing the list of loans in JSON format.
     */
    public function list(): JsonResponse
    {
        $loans = Loan::with('member', 'librarian')->get();

        return response()->json([
            'data' => LoanResource::collection($loans)
        ])->setStatusCode(200);
    }

    /**
     * Search for loans with filter.
     *
     * @param LoanSearchRequest $request
     * @return LoanCollection
     */
    public function search(LoanSearchRequest $request): LoanCollection
    {
        $data = $request->validated();

        $size = data_get($data, 'page_size', 10);

        $loans = Loan::where(function (Builder $query) use ($data) {
            $member_id = data_get($data, 'member_id', '');
            $librarian_id = data_get($data, 'librarian_id', '');
            $loan_date_start = data_get($data, 'loan_date_start', '');
            $loan_date_end = data_get($data, 'loan_date_end', '');
            $return_date_start = data_get($data, 'return_date_start', '');
            $return_date_end = data_get($data, 'return_date_end', '');

            if ($member_id) {
                $query->where(function (Builder $query) use ($member_id) {
                    $query->orWhere('member_id', $member_id);
                });
            }
            if ($librarian_id) {
                $query->where(function (Builder $query) use ($librarian_id) {
                    $query->orWhere('librarian_id', $librarian_id);
                });
            }
            if ($loan_date_start && $loan_date_end) {
                $query->where(function (Builder $query) use ($loan_date_start, $loan_date_end) {
                    $query->orWhereBetween('loan_date', [$loan_date_start, $loan_date_end]);
                });
            }
            if ($return_date_start && $return_date_end) {
                $query->where(function (Builder $query) use ($return_date_start, $return_date_end) {
                    $query->orWhereBetween('loan_date', [$return_date_start, $return_date_end]);
                });
            }
        })->paginate($size);

        return new LoanCollection($loans);
    }

    /**
     * Retrieve a loan by its ID.
     *
     * @param int $id ID of the loan
     * @return LoanResource The loan in JSON format
     * @throws HttpResponseException If the loan is not found
     */
    public function get(int $id): LoanResource
    {
        $loan = $this->getLoan($id);
        return new LoanResource($loan);
    }

    /**
     * Update a loan.
     *
     * @param int $id ID of the loan
     * @param LoanCreateRequest $request Request containing the new loan data
     * @return JsonResponse The updated loan in JSON format
     * @throws HttpResponseException If the loan is not found
     */
    public function update(int $id, LoanCreateRequest $request): JsonResponse
    {
        $loan = $this->getLoan($id);
        $data = $request->validated();
        $loan->fill($data);
        $loan->save();
        return (new LoanResource($loan))->response()->setStatusCode(200);
    }

    /**
     * Delete a loan by its ID.
     *
     * @param int $id ID of the loan
     * @return JsonResponse The response containing a boolean indicating whether the deletion was successful
     * @throws HttpResponseException If the loan is not found
     */
    public function delete(int $id): JsonResponse
    {
        $loan = $this->getLoan($id);
        $loan->delete();
        return response()->json(['data' => true], 200);
    }
}
