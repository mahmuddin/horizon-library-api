<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * Retrieves a contact by its ID for a given user.
     *
     * @param int $idContact The ID of the contact to retrieve.
     * @param User $user The user to whom the contact belongs.
     * @return Contact The contact associated with the given ID and user.
     * @throws HttpResponseException If no contact is found, throws an exception with a 404 response.
     */
    public function getContacts(int $idContact, User $user): Contact
    {
        $contact = Contact::where('id', $idContact)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ], 404));
        }
        return $contact;
    }

    /**
     * Retrieves an address by its ID for a given contact.
     *
     * @param Contact $contact The contact associated with the address.
     * @param int $idAddress The ID of the address to retrieve.
     * @return Address The address associated with the given ID and contact.
     * @throws HttpResponseException If no address is found, throws an exception with a 404 response.
     */
    public function getAddress(Contact $contact, int $idAddress): Address
    {
        /**
         * Retrieves an address by its ID for a given contact.
         *
         * @param Contact $contact The contact associated with the address.
         * @param int $idAddress The ID of the address to retrieve.
         * @return Address The address associated with the given ID and contact.
         * @throws HttpResponseException If no address is found, throws an exception with a 404 response.
         */
        $address = Address::where('id', $idAddress)->where('contact_id', $contact->id)->first();
        if (!$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ], 404));
        }
        return $address;
    }

    /**
     * Creates a new address for a given contact and returns a JSON response with the address's information.
     *
     * @param int $idContact The ID of the contact to associate with the new address.
     * @param AddressCreateRequest $request The request with the address's data.
     * @return AddressResource The new address in JSON format.
     */
    public function create(int $idContact, AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContacts($idContact, $user);

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();
        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    /**
     * Retrieves an address by its ID for a given contact and user.
     *
     * @param int $idContact The ID of the contact associated with the address.
     * @param int $idAddress The ID of the address to retrieve.
     * @return AddressResource The address resource associated with the given IDs.
     * @throws HttpResponseException If no contact or address is found, throws an exception with a 404 response.
     */
    public function get(int $idContact, int $idAddress): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContacts($idContact, $user);
        $address = $this->getAddress($contact, $idAddress);
        return new AddressResource($address);
    }

    /**
     * Updates an address for a given contact and returns the updated address as a resource.
     *
     * @param int $idContact The ID of the contact associated with the address.
     * @param int $idAddress The ID of the address to update.
     * @param AddressUpdateRequest $request The request containing the updated address data.
     * @return AddressResource The updated address in JSON format.
     */
    public function update(int $idContact, int $idAddress, AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContacts($idContact, $user);
        $address = $this->getAddress($contact, $idAddress);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
    }

    /**
     * Deletes an address for a given contact and returns a JSON response.
     *
     * @param int $idContact The ID of the contact associated with the address.
     * @param int $idAddress The ID of the address to delete.
     * @return JsonResponse A JSON response indicating the success of the operation.
     * @throws HttpResponseException If no contact or address is found, throws an exception with a 404 response.
     */
    public function delete(int $idContact, int $idAddress): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContacts($idContact, $user);
        $address = $this->getAddress($contact, $idAddress);
        $address->delete();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }


    /**
     * Retrieves a list of addresses for a given contact and user.
     *
     * @param int $idContact The ID of the contact associated with the addresses.
     * @return JsonResponse A JSON response containing the list of addresses in JSON format.
     * @throws HttpResponseException If no contact or address is found, throws an exception with a 404 response.
     */
    public function list(int $idContact): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContacts($idContact, $user);

        $address = Address::where('contact_id', $contact->id)->get();

        return response()->json([
            'data' => AddressResource::collection($address)
        ])->setStatusCode(200);
    }
}
