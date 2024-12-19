<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use Illuminate\Http\Request;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ContactController extends Controller
{
    /**
     * @param int $idContact
     * @param User $user
     * @return Contact
     * @throws HttpResponseException
     */
    private function getContacts(int $idContact, User $user): Contact
    {
        // Find the contact
        $contact = Contact::where('id', $idContact)->where('user_id', $user->id)->first();
        // If the contact is not found, return a 404 response
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'contact' => ['Contact not found.']
                ]
            ], 404));
        }
        return $contact;
    }

    /**
     * Creates a new contact for the authenticated user and returns a JSON response with the contact's information.
     *
     * @param ContactCreateRequest $request
     * @return JsonResponse
     */
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }


    /**
     * Searches for contacts of the authenticated user based on query parameters
     * such as name, phone, and email. The results are paginated.
     *
     * @param Request $request The HTTP request instance containing query parameters.
     * @return ContactCollection A collection of contacts that match the search criteria.
     */
    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->query('page', 1);
        $size = $request->input('size', 10);
        $contacts = Contact::where('user_id', $user->id)
            ->where(function (Builder $query) use ($request) {
                $name = $request->input('name');
                $phone = $request->input('phone');
                $email = $request->input('email');
                if ($name) {
                    $query->where(function (Builder $query) use ($name) {
                        $query->orWhere('first_name', 'ilike', '%' . $name . '%')
                            ->orWhere('last_name', 'ilike', '%' . $name . '%');
                    });
                }
                if ($phone) {
                    $query->where('phone', 'ilike', '%' . $phone . '%');
                }
                if ($email) {
                    $query->where('email', 'ilike', '%' . $email . '%');
                }
            })
            ->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
    }

    /**
     * Retrieves a contact by id.
     *
     * @param int $id The contact's id.
     * @return ContactResource
     */
    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = $this->getContacts($id, $user);
        return new ContactResource($contact);
    }

    /**
     * Updates a contact.
     *
     * @param int $id The contact's id.
     * @param ContactUpdateRequest $request
     * @return ContactResource
     */
    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();
        $contact = $this->getContacts($id, $user);
        $data = $request->validated();
        $contact->fill($data);
        $contact->save();
        return new ContactResource($contact);
    }

    /**
     * Deletes a contact.
     *
     * @param int $id The contact's id.
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContacts($id, $user);
        $contact->delete();
        return response()->json(['data' => true], 200);
    }
}
