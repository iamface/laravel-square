<?php

namespace Iamface\LaravelSquare;

use App\Http\Controllers\Controller;
use SquareConnect\Api\CustomersApi;
use SquareConnect\Api\LocationsApi;
use SquareConnect\Configuration;

class LaravelSquareController extends Controller
{
    private $_currency;
    private $_locations;
    private $_locationName;

    public function __construct()
    {
        $this->_currency = config('laravelSquare.currency');
        $this->_locationName = config('laravelSquare.location');

        // Set Square token
        Configuration::getDefaultConfiguration()->setAccessToken(config('laravelSquare.token'));

        $this->_locations = $this->_getLocations();
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

        $customerRecord = new \stdClass();

        foreach ($customers as $customer) {
            if ($customer->{$method}() === $param) {
                $customerRecord = $customer;
                break;
            }
        }

        return $customerRecord;
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
     * @return \SquareConnect\Model\Location[] {Array}
     */
    private function _getLocations()
    {
        $locationsAPI = new LocationsApi();

        $locations = $locationsAPI->listLocations()->getLocations();

        $locs = array();
        foreach ($locations as $location) {
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
        }

        return $locs;
    }
}