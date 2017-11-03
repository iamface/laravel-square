<?php

namespace Iamface\LaravelSquare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use SquareConnect\Api\CustomersApi;
use SquareConnect\Api\LocationsApi;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\Configuration;
use SquareConnect\Model\ChargeRequest;
use Validator;

class LaravelSquareController extends Controller
{
    const CUSTOMER_NOT_FOUND   = 'Customer not found!';
    const NO_CUSTOMERS_FOUND   = 'No customers found!';
    const AUTHORIZATION_FAILED = 'Unable to create authorization.';

    private $_currency;
    private $_locations;
    private $_locationName;

    public function __construct()
    {
        if (Config::has('laravelSquare')) {
            $this->_currency = config('laravelSquare.currency');
            $this->_locationName = config('laravelSquare.location');

            // Set Square token
            Configuration::getDefaultConfiguration()->setAccessToken(config('laravelSquare.token'));

            $this->_locations = $this->_getLocations(config('laravelSquare.non_capable_locations'));
        }
    }

    /**
     * Returns all customer records
     *
     * @param Request $request
     *
     * @return \SquareConnect\Model\Customer[] {Object}
     */
    public function getCustomers(Request $request) {
        $customers = $this->_getCustomers();

        $input = $request->input();

        $cust = [];

        if (count($customers)) {
            foreach ($customers as $customer) {
                // Parameters used
                if (count($input)) {
                    $c = [];

                    foreach (explode(',', $input['info']) as $data) {
                        switch ($data) {
                            case 'id': $c[$data] = $customer->getId(); break;
                            case 'created_at': $c[$data] = $customer->getCreatedAt(); break;
                            case 'updated_at': $c[$data] = $customer->getUpdatedAt(); break;
                            case 'cards': $c[$data] = $customer->getCards(); break;
                            case 'first_name': $c[$data] = $customer->getGivenName(); break;
                            case 'last_name': $c[$data] = $customer->getFamilyName(); break;
                            case 'nickname': $c[$data] = $customer->getNickname(); break;
                            case 'company': $c[$data] = $customer->getCompanyName(); break;
                            case 'email': $c[$data] = $customer->getEmailAddress(); break;
                            case 'address': $c[$data] = $customer->getAddress(); break;
                            case 'phone': $c[$data] = $customer->getPhoneNUmber(); break;
                            case 'reference_id': $c[$data] = $customer->getReferenceId(); break;
                            case 'note': $c[$data] = $customer->getNote(); break;
                            case 'preferences': $c[$data] = $customer->getPreferences(); break;
                            case 'groups': $c[$data] = $customer->getGroups(); break;
                        }
                    }
                } else { // Return all nodes
                    $c = [
                        'id'           => $customer->getId(),
                        'created_at'   => $customer->getCreatedAt(),
                        'updated_at'   => $customer->getUpdatedAt(),
                        'cards'        => $customer->getCards(),
                        'first_name'   => $customer->getGivenName(),
                        'last_name'    => $customer->getFamilyName(),
                        'nickname'     => $customer->getNickname(),
                        'company'      => $customer->getCompanyName(),
                        'email'        => $customer->getEmailAddress(),
                        'address'      => $customer->getAddress(),
                        'phone'        => $customer->getPhoneNumber(),
                        'reference_id' => $customer->getReferenceId(),
                        'note'         => $customer->getNote(),
                        'preferences'  => $customer->getPreferences(),
                        'groups'       => $customer->getGroups()
                    ];
                }

                // Add customer to return response
                array_push($cust, $c);
            }

            return response()->json($cust);
        } else {
            // No customers found
            return LaravelSquareError::throwError(['message' => self::NO_CUSTOMERS_FOUND], 404);
        }
    }

    /**
     * Returns a customer record
     *
     * @param $param {String}
     *
     * @return \SquareConnect\Model\Customer {Object}
     */
    public function getCustomer($param) {
        // Determine identifier by either email address or customer id
        $identifier = (strpos($param, '@')) ? 'email_address' : 'id';

        $customers = $this->_getCustomers();

        $method = null;
        switch ($identifier) {
            case 'email_address':
                $method = 'getEmailAddress';
                break;
            case 'id':
                $method = 'getId';
                break;
        }

        if (count($customers)) {
            foreach ($customers as $customer) {
                if ($customer->{$method}() === $param) {
                    // Customer match found
                    return response()->json(json_decode($customer));
                }
            }

            // No customer match found
            return LaravelSquareError::throwError(['message' => self::CUSTOMER_NOT_FOUND], 404);
        } else {
            // No customers found
            return LaravelSquareError::throwError(['message' => self::NO_CUSTOMERS_FOUND], 404);
        }
    }

    /**
     * Authorize card for a transaction
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorizeCard(Request $request) {
        $validator = Validator::make($request->all(), [
            'nonce'  => 'required|string',
            'amount' => 'required'
        ]);

        // Validate input
        if ($validator->fails()) {
            return $validator->errors();
        }

        $input = $request->input();

        $transaction = new TransactionsApi();

        // Create charge body
        $body = new ChargeRequest([
            'card_nonce' => $input['nonce'],
            'amount_money' => [
                'amount' => $input['amount'],
                'currency' => $this->_currency
            ],
            'idempotency_key' => uniqid()
        ]);

        try {
            $transaction->charge($this->_locationName, $body)->getTransaction();
        } catch (\Exception $e) {
            return LaravelSquareError::throwError(['message' => self::AUTHORIZATION_FAILED], 422);
        }
    }

    /**
     * List locations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listLocations() {
        $locations = [];

        // If at least one location exists
        if (count($this->_locations)) {
            foreach ($this->_locations as $location) {
                // If the location has an address listed
                if ($address = $location->getAddress()) {
                    $addressArray = [
                        'address_line_1'                  => $address->getAddressLine1(),
                        'address_line_2'                  => $address->getAddressLine2(),
                        'address_line_3'                  => $address->getAddressLine3(),
                        'locality'                        => $address->getLocality(),
                        'sublocality'                     => $address->getSublocality(),
                        'sublocality_2'                   => $address->getSublocality2(),
                        'sublocality_3'                   => $address->getSublocality3(),
                        'administrative_district_level_1' => $address->getAdministrativeDistrictLevel1(),
                        'administrative_district_level_2' => $address->getAdministrativeDistrictLevel2(),
                        'administrative_district_level_3' => $address->getAdministrativeDistrictLevel3(),
                        'postal_code'                     => $address->getPostalCode(),
                        'country'                         => $address->getCountry(),
                        'first_name'                      => $address->getFirstName(),
                        'last_name'                       => $address->getLastName(),
                        'organization'                    => $address->getOrganization()
                    ];
                }

                $location = [
                    'id'           => $location->getId(),
                    'name'         => $location->getName(),
                    'address'      => (isset($addressArray)) ? $addressArray : [],
                    'timezone'     => $location->getTimezone(),
                    'capabilities' => $location->getCapabilities()
                ];

                // Add location to return response
                array_push($locations, $location);
            }

            return response()->json($locations);
        } else {
            return LaravelSquareError::throwError(['message' => 'No locations found.'], 404);
        }
    }

    public function listTransactions($param) {
        // TODO list transactions
        dd($param);
    }

    public function listAuthorizations() {
        // TODO return a list of authorizations by location
    }

    private function _saveCard() {
        // TODO save card on file
    }

    private function _removeCard() {
        // TODO remove card on file
    }

    /**
     * Returns an array of customer records
     *
     * @return \SquareConnect\Model\Customer[] {Array}
     */
    private function _getCustomers() {
        $customersAPI = new CustomersApi();

        return $customersAPI->listCustomers()->getCustomers();
    }

    private function _saveCustomer() {
        // TODO save new customer
    }

    /**
     * Returns an array of locations
     *
     * @param $non_capable_locations {Boolean}
     * Include non-capable credit card processing locations
     *
     * @return \SquareConnect\Model\Location[] {Array}
     */
    private function _getLocations($non_capable_locations)
    {
        $locationsAPI = new LocationsApi();

        $locations = $locationsAPI->listLocations()->getLocations();

        $locs = [];
        foreach ($locations as $location) {

            if (!$non_capable_locations) {
                // Get location capabilities
                $capabilities = $location->getCapabilities();

                if (isset($capabilities)) {
                    foreach ($capabilities as $capability) {
                        // Only use locations that have credit card processing
                        if ($capability === 'CREDIT_CARD_PROCESSING') {
                            array_push($locs, $location);
                        }
                    }
                }
            } else { // Include all locations whether or not they can process credit cards
                array_push($locs, $location);
            }
        }

        return $locs;
    }
}