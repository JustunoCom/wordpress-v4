<?php

namespace Integrations;

require_once dirname(__FILE__) . '/integrations/JustWooCommerce.php';

if (!class_exists('ju4_JustRESTManager')) {
    class ju4_JustRESTManager
    {
        public $JustWooService = null;

        public function __construct()
        {
            $this->JustWooService = new ju4_JustWooCommerce();
        }

        public function verifyWooCommerceToken($haders)
        {
            if ($_GET['type'] == 'cart' || $_GET['type'] == 'product-single') {
                return true;
            } else if (isset($haders['HTTP_AUTHORIZATION'])) {
                return str_replace("Bearer ", "", $haders['HTTP_AUTHORIZATION']) == get_option('ju4_justuno_woocommerce_token');
            }

            return false;
        }

        public function entertainCall()
        {
            try {
                try {
                    $request = $_GET;
                    $data = [];
                    $isVerifiedToken = $this->verifyWooCommerceToken($_SERVER);
                    if ($isVerifiedToken === true) {
                        if ($request['type'] === 'verbose') {
                            $this->getVerboseData($request);
                            http_response_code(200);
                            exit;
                        }

                        if ($request['type'] === 'discount') {
                            $return = $this->applyDiscountData();
                            if ($return === false) {
                                header("HTTP/1.1 401 Unauthorized");
                                echo json_encode(['message' => 'Invalid Code.']);
                                exit;
                            } else {
                                $data = ['message' => 'Cuopon Applied successfully.'];
                            }
                        } else if ($request['type'] === 'cart') {
                            $data = $this->getCartData();
                        } else if ($request['type'] === 'order') {
                            $data = $this->getOrderData($request);
                        } else if ($request['type'] === 'product-single') {
                            $data = $this->getSingleProduct($request);
                        } else {
                            $data = $this->getProductData($request);
                        }
                        $data = $this->array_filter_recursive($data);
                        http_response_code(200);
                        echo json_encode($data);
                        exit;
                    }
                    header("HTTP/1.1 401 Unauthorized");
                    echo json_encode(['message' => 'Invalid token.']);
                    exit;
                } catch (\Exception $e) {
                    if ($_GET['debug'] == true) {
                        print_r($e);
                        exit;
                    }
                }
            } catch (\Error $e) {
                if ($_GET['debug'] == true) {
                    print_r($e);
                    exit;
                }
            }
        }

        public function getVerboseData($data)
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getVerboseData($data);
            }
        }

        public function getProductData($data)
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getProductData($data);
            }
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['message' => 'No Ecommerce plugin such as WooCommerce is active.']);
            exit;
        }

        public function getOrderData($data)
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getOrderData($data);
            }
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['message' => 'No Ecommerce plugin such as WooCommerce is active.']);
            exit;
        }

        public function applyDiscountData()
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->applyDiscountData($_GET);
            }
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['message' => 'No Ecommerce plugin such as WooCommerce is active.']);
            exit;
        }

        public function getCartData()
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getCartData();
            }
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['message' => 'No Ecommerce plugin such as WooCommerce is active.']);
            exit;
        }

        public function getSingleProduct($data)
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getSingleProduct($data["id"]);
            }
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['message' => 'No Ecommerce plugin such as WooCommerce is active.']);
            exit;
        }

        public function getConversionTrackingCodes()
        {
            if (class_exists('woocommerce')) {
                return $this->JustWooService->getConversionTrackingCodes();
            }
        }

        public function array_filter_recursive($input)
        {
            foreach ($input as &$value) {
                if (is_array($value)) {
                    $value = $this->array_filter_recursive($value);
                }
            }

            return array_filter($input);
        }
    }
}
