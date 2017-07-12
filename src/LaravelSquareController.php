<?php

namespace Iamface\LaravelSquare;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use SquareConnect\Api\CustomersApi;
use SquareConnect\Api\LocationsApi;
use SquareConnect\Configuration;

class LaravelSquareController extends Controller
{
    const CUSTOMER_NOT_FOUND = 'Customer not found!';

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

    public function index()
    {
        dd($this->_locations);
    }

    /**
     * Returns all customer records
     *
     * @return \SquareConnect\Model\Customer[] {Object}
     */
    public function getCustomers() {
        return $this->_getCustomers();
    }

    /**
     * Returns a customer record
     *
     * @param $param {String}
     *
     * @return \SquareConnect\Model\Customer {Object}
     */
    public function getCustomer($param) {
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

        foreach ($customers as $customer) {
            if ($customer->{$method}() === $param) {
                return response()->json(json_decode($customer));
            }
        }

        return LaravelSquareError::throwError(['message' => self::CUSTOMER_NOT_FOUND], 404);
    }

    public function authorizeCard() {
        // TODO authorize card for transaction
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

        $locs = array();
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