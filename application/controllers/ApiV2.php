<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Order_model');
        $this->load->library('session');
        $this->checklogin = $this->session->userdata('logged_in');
        $session_user = $this->session->userdata('logged_in');
        if ($session_user) {
            $this->user_id = $session_user['login_id'];
        } else {
            $this->user_id = 0;
        }
    }

    public function index() {
        $this->load->view('welcome_message');
    }

    function updateCurd_post() {
        $fieldname = $this->post('name');
        $value = $this->post('value');
        $pk_id = $this->post('pk');
        $tablename = $this->post('tablename');
        if ($this->checklogin) {
            $data = array($fieldname => $value);
            $this->db->set($data);
            $this->db->where("id", $pk_id);
            $this->db->update($tablename, $data);
        }
    }

    //function for product list
    function loginOperation_get() {
        $userid = $this->user_id;
        $this->db->select('au.id,au.first_name,au.last_name,au.email,au.contact_no');
        $this->db->from('admin_users au');
        $this->db->where('id', $userid);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row();
        $this->response($result);
    }

    //Login Function 
    //function for product list
    function loginOperation_post() {
        $email = $this->post('contact_no');
        $password = $this->post('password');
        $this->db->select('au.id,au.first_name,au.last_name,au.email,au.contact_no');
        $this->db->from('admin_users au');
        $this->db->where('contact_no', $email);
        $this->db->where('password', md5($password));
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row();

        $sess_data = array(
            'username' => $result->email,
            'first_name' => $result->first_name,
            'last_name' => $result->last_name,
            'login_id' => $result->id,
        );
        $this->session->set_userdata('logged_in', $sess_data);
        $this->response($result);
    }

    function registerMobileGuest_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $reg_id = $this->post('reg_id');
        $model = $this->post('model');
        $manufacturer = $this->post('manufacturer');
        $uuid = $this->post('uuid');
        $regArray = array(
            "reg_id" => $reg_id,
            "manufacturer" => $manufacturer,
            "uuid" => $uuid,
            "model" => $model,
            "user_id" => "Guest",
            "user_type" => "Guest",
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->where('reg_id', $reg_id);
        $query = $this->db->get('gcm_registration');
        $regarray = $query->result_array();
        if ($regArray) {
            
        } else {
            $this->db->insert('gcm_registration', $regArray);
        }
        $this->response(array("status" => "done"));
    }

    function registration_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $name = $this->post('name');
        $email = $this->post('email');
        $contact_no = $this->post('contact_no');
        $password = $this->post('password');
        $usercode = rand(10000000, 99999999);
        $regArray = array(
            "name" => $name,
            "email" => $email,
            "contact_no" => $contact_no,
            "password" => $password,
            "usercode" => $usercode,
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->where('email', $email);
        $query = $this->db->get('app_user');
        $userdata = $query->row();
        if ($userdata) {
            $this->response(array("status" => "already", "userdata" => ""));
        } else {
            $this->db->insert('app_user', $regArray);
            $this->response(array("status" => "done", "userdata" => $regArray));
        }
    }

    function registrationMob_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $name = $this->post('name');
        $contact_no = $this->post('contact_no');
        $usercode = rand(10000000, 99999999);
        $regArray = array(
            "name" => $name,
            "email" => "",
            "contact_no" => $contact_no,
            "password" => $usercode,
            "usercode" => $usercode,
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->where('contact_no', $contact_no);
        $query = $this->db->get('app_user');
        $userdata = $query->row();
        if ($userdata) {
            $profiledata = array(
                'name' => $this->post('name'),
                'contact_no' => $this->post('contact_no'),
            );
            $this->db->set($profiledata);
            $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
            $this->db->update("app_user");
            $this->response(array("status" => "already", "userdata" => $userdata));
        } else {
            $this->db->insert('app_user', $regArray);
            $this->response(array("status" => "done", "userdata" => $regArray));
        }
    }

    function loginmob_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $email = $this->post('contact_no');
        $password = $this->post('password');
        $regArray = array(
            "email" => $email,
            "password" => $password,
        );
        $this->db->where('email', $email);
        $this->db->where('password2', $password);
        $query = $this->db->get('admin_users');
        $userdata = $query->row();
        if ($userdata) {
            $this->response(array("status" => "done", "userdata" => $userdata));
        } else {
            $this->response(array("status" => "error", "userdata" => ""));
        }
    }

    function updateProfile_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $contact_no = $this->post('contact_no');
        $profiledata = array(
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'contact_no' => $this->post('contact_no'),
        );
        $this->db->set($profiledata);
        $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
        $this->db->update("app_user");
        $this->db->order_by('name asc');

        $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
        $query = $this->db->get('app_user');
        $userData = $query->row();
        $this->response(array("userdata" => $userData));
    }

    //function for product list
    function userbooking_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $email = $this->post('email');
        $this->db->order_by('id desc');
        $this->db->where('email', $email);
        $query = $this->db->get("web_order");
        $result = $query->result();
        $this->response($result);
    }

//
    //function for product list
    function userorder_get($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('id desc');
        $query = $this->db->get("user_order");
        $order_mobile = $query->result_array();
//        $orderlist = [];
//        foreach ($order_mobile as $key => $value) {
//            $orderid = $value['id'];
//            $this->db->where('order_id', $orderid);
//            $query = $this->db->get("ordercart");
//            $cartdata = $query->result_array();
//            $value['cart_data'] = $cartdata;
//            array_push($orderlist, $value);
//        }
        $this->response($order_mobile);
    }

    //function for order details list
    function userorderdetails_get($order_id) {
        $this->db->where('id', $order_id);
        $this->db->order_by('id desc');
        $query = $this->db->get("user_order");
        $order_mobile = $query->row();
        $orderdetails = array("order" => $order_mobile);

        $orderid = $order_mobile->id;
        $this->db->where('order_id', $order_id);
        $query = $this->db->get("cart");
        $cartdata = $query->result_array();
        $orderdetails['cart_data'] = $cartdata;

        $this->db->order_by('id', 'desc');
        $this->db->where('order_id', $order_id);
        $query = $this->db->get('user_order_status');
        $userorderstatus = $query->result();
        $orderdetails['order_status'] = $userorderstatus;

        $this->response($orderdetails);
    }

    //-----------
    //function for product list

    function category_get() {
        $cats = [65, 67, 69, 70, 71, 73];
        $cats = [1, 2, 3, 4, 5, 6, 7, 8];
        $this->config->load('rest', TRUE);
        $this->db->where("parent_id=0");
        $query = $this->db->get("category");
        $galleryList = $query->result();
        $this->response($galleryList);
    }

    function productCategoryAll_get() {
        $this->config->load('rest', TRUE);
        $query = $this->db->get("category");
        $galleryList = $query->result();
        $this->response($galleryList);
    }

    function productCategory_get($category_id) {
        $this->config->load('rest', TRUE);
        $categorieslist = $this->Product_model->get_children($category_id, array());
        $this->response($categorieslist);
    }

    function productDetails_get($prodct_id) {
        $this->config->load('rest', TRUE);
        $this->db->where_in("id", $prodct_id);
        $query = $this->db->get("products");
        $productlist = $query->row();
        $this->response($productlist);
    }

    function mobilebrands_get() {
        $cats = [74, 75, 78, 77];
        $this->config->load('rest', TRUE);
        $this->db->where_in("id", $cats);
        $query = $this->db->get("category");
        $galleryList = $query->result();
        $catelist = [];
        foreach ($galleryList as $key => $value) {
            if ($key % 2 == 0) {
                $templist = [$galleryList[$key], $galleryList[$key + 1]];
                array_push($catelist, $templist);
            }
        }
        $this->response($catelist);
    }

    function productListSearch_get() {
        $this->config->load('rest', TRUE);
        $search = $this->get('search');
        $this->db->where("title like '%$search%'");
        $this->db->where("status", '1');
//        $this->db->where_in("stock_status", 'In Stock');
        $query = $this->db->get("products");
        $productlist = $query->result();
        $this->response($productlist);
    }

    function productList_get($categoryid) {
        $this->config->load('rest', TRUE);
        $categoriesString = $this->Product_model->stringCategories($categoryid);
        $categoriesString = ltrim($categoriesString, ", ");
        $categorylist = explode(", ", $categoriesString);
        if ($categoriesString) {
            $categorylist = $categorylist;
        } else {
            $categorylist = [];
        }
        array_push($categorylist, $categoryid);
        $this->db->where_in("category_id", $categorylist);
        $this->db->where("status", '1');
//        $this->db->where_in("stock_status", 'In Stock');
        $query = $this->db->get("products");
        $productlist = $query->result();
        $this->response($productlist);
    }

    function productListOffers_get($categoryid) {
        $this->config->load('rest', TRUE);
        $categoriesString = $this->Product_model->stringCategories($categoryid);
        $categoriesString = ltrim($categoriesString, ", ");
        $categorylist = explode(", ", $categoriesString);
        if ($categoriesString) {
            $categorylist = $categorylist;
        } else {
            $categorylist = [];
        }
        array_push($categorylist, $categoryid);
        $this->db->where_in("category_id", $categorylist);
        $this->db->where("status", '1');
        $this->db->where("offer", '1');
//        $this->db->where_in("stock_status", 'In Stock');
        $query = $this->db->get("products");
        $productlist = $query->result();
        $this->response($productlist);
    }

    function productListOffersFront_get($categoryid) {
        $this->config->load('rest', TRUE);
        $categoriesString = $this->Product_model->stringCategories($categoryid);
        $categoriesString = ltrim($categoriesString, ", ");
        $categorylist = explode(", ", $categoriesString);
        if ($categoriesString) {
            $categorylist = $categorylist;
        } else {
            $categorylist = [];
        }
        array_push($categorylist, $categoryid);
        $this->db->where_in("category_id", $categorylist);
        $this->db->where("status", '1');
        $this->db->where("offer", '1');
        $this->db->limit(10);
        $query = $this->db->get("products");
        $productlist = $query->result();
        $this->response($productlist);
    }

    function enquiry_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $enquiry = array(
            'name' => $this->post('name'),
            'message' => $this->post('message'),
            'email' => $this->post('email'),
            'contact' => $this->post('contact_no'),
        );

        $this->db->insert('web_enquiry', $enquiry);
    }

    function shippingAmt_get($zipcode) {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $this->db->where("attr_key", 'static_shipping_price');
        $query = $this->db->get("configuration_attr");
        $shippingprice = $query->row();
        $this->response($shippingprice);
    }

    function paymentInstamojo_get() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.instamojo.com/api/1.1/payment-requests/');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                array("X-Api-Key:7987c263e708a6a7c88eebe6701dc834",
                    "X-Auth-Token:ad1c848926d896c37a2d0dad200261c2"));
        $payload = Array(
            'purpose' => 'FIFA 16',
            'amount' => '2500',
            'phone' => '9999999999',
            'buyer_name' => 'John Doe',
            'redirect_url' => 'http://www.example.com/redirect/',
            'send_email' => true,
            'webhook' => 'http://www.example.com/webhook/',
            'send_sms' => true,
            'email' => 'foo@example.com',
            'allow_repeated_payments' => false
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        curl_close($ch);
        $this->response($response);
    }

    //new ecome api

    function getProductBySerialNo_get($serial_no, $plumber_id) {
        $this->db->where("serial_no", $serial_no);
        $query = $this->db->get('product_stock');
        $serial_obj = $query->row_array();
        if ($serial_obj) {
            $this->db->where("id", $serial_obj["product_id"]);
            $query = $this->db->get('products');
            $productobj = $query->row_array();
            $imageurl = base_url() . "assets/default/default.png";
            if ($productobj) {
                if ($productobj['file_name']) {
                    $imageurl = base_url() . "assets/product_images/" . $productobj['file_name'];
                }
                $productobj['file_name'] = $imageurl;
                $this->db->where("serial_no", $serial_no);
                $query = $this->db->get('product_rewards');
                $serial_objcheck = $query->row_array();
                if ($serial_objcheck) {
                    $this->response(array(
                        "status" => "300",
                        "message" => "Product already has been used."
                    ));
                } else {
                    $credit_points = $productobj['credit_limit'];
                    $reward_array = array(
                        "plumber_id" => $plumber_id,
                        "product_id" => $productobj["id"],
                        "serial_no" => $serial_no,
                        "stock_id" => $serial_obj["id"],
                        "points" => $credit_points,
                        "points_type" => "Credit",
                        "date" => Date("Y-m-d"),
                        "time" => Date("H:m:s A"),
                    );
                    $this->db->insert("product_rewards", $reward_array);

                    $this->response(array(
                        "status" => "200",
                        "message" => "$credit_points reward points have been credited in your account",
                        "product_info" => $productobj
                    ));
                }
            } else {
                $this->response(array("status" => "404", "message" => "Invalid Product, Please try again"));
            }
        } else {
            $this->response(array("status" => "404", "message" => "Invalid Product, Please try again"));
        }
    }

    //Ecome setup
    function getCategoryList_get() {
        $result = $this->db->where('parent_id', '0')->get('category')->result_array();
        $limit = count($result);
        $limit1 = ((int) ($limit / 9));
        $limit2 = $limit % 9;
        $sublimit = $limit2 ? 9 - $limit2 : 0;
        $flimit = $limit + $sublimit;
        $rangelimit = range(0, $flimit, 9);
        $resultdata = [];
        foreach ($result as $key => $value) {
            $tempdata = array();
            $tempdata["title"] = $value["category_name"];
            $tempdata["image"] = $value["image"] ? base_url() . "assets/media/" . $value["image"] : base_url() . "assets/default/default.png";
            $tempdata["category_name"] = $value["category_name"];
            $tempdata["category_id"] = $value["id"];
            $sub_category = $this->db->get_where('category', array('parent_id' => $value["id"]))->result_array();
            $tempdata["sub_category"] = $sub_category;
            $tempsubcat = [];
            foreach ($sub_category as $sbkey => $sbvalue) {
                array_push($tempsubcat, $sbvalue["category_name"]);
            }
            $tempdata["sub_category_str"] = implode(", ", $tempsubcat);
            array_push($resultdata, $tempdata);
        }
        $this->response($resultdata);
    }

    function getProductsList_get($category_id, $limit = 20, $startpage = 0) {
        $categoriesString = $this->Product_model->stringCategories($category_id) . ", " . $category_id;
        $categoriesString = ltrim($categoriesString, ", ");
        $product_query = "select pt.id as product_id,  ct.category_name, pt.*
            from products as pt 
            join category as ct on ct.id = pt.category_id 
            where pt.category_id in ($categoriesString) and variant_product_of = ''  order by id
              limit $startpage, $limit";
        $product_result = $this->Product_model->query_exe($product_query);
        $finallist = [];
        foreach ($product_result as $key => $value) {
            $productobj = $value;
            $imageurl = base_url() . "assets/default/default.png";
            if ($productobj['file_name']) {
                $imageurl = base_url() . "assets/product_images/" . $productobj['file_name'];
            }
            $productobj["image"] = $imageurl;
            $category = $this->Product_model->parent_get($productobj["category_id"]);
            if ($category) {
                $productobj["category_nav"] = $category["category_string"];
            }

            $price = $productobj["price"];

            $productobj["price"] = "INR " . number_format($price, 2, '.', '');
            $productobj["fprice"] = $price;
            array_push($finallist, $productobj);
        }

        $this->response($finallist);
    }

    function shippingAddress_get($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('id desc');
        $query = $this->db->get("shipping_address");
        $shipping_address = $query->result_array();
        $this->response($shipping_address);
    }

    function shippingAddress_post() {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header('Access-Control-Allow-Origin: *');
        $this->config->load('rest', TRUE);
        $shippingAddress = array(
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'contact_no' => $this->post('contact_no'),
            'user_id' => $this->post('user_id') ? $this->post('user_id') : 'Guest',
            'zipcode' => $this->post('zipcode') ? $this->post('zipcode') : "",
            'address1' => $this->post('address1'),
            'address2' => "",
            'city' => "",
            'state' => "",
            'country' => "",
        );
        $this->db->insert('shipping_address', $shippingAddress);
        $last_id = $this->db->insert_id();
        $this->response(array("last_id" => $last_id));
    }

    function applyCoupon_get() {
        $this->db->where("valid_till>=", date("Y-m-d"));
        $querycoupon = $this->db->get("coupon_conf");
        $couopndata = [];
        if ($querycoupon) {
            $couopndata = $querycoupon->result_array();
        }
        $this->response($couopndata);
    }

    function applyCoupon_post() {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header('Access-Control-Allow-Origin: *');
        $this->config->load('rest', TRUE);
        $total_amount = $this->post("total_amount");
        $couponcode = $this->post("couponcode");
        $couponarray = array(
            "has_coupon" => 0,
            "coupon_code" => "",
            "coupon_discount" => "0",
            "coupon_discount_type" => "",
            "coupon_message" => "",
        );
        if ($couponcode) {
            $this->db->where("code", $couponcode);
            $this->db->where("valid_till>=", date("Y-m-d"));
            $querycoupon = $this->db->get("coupon_conf");
            if ($querycoupon) {
                $couopndata = $querycoupon->row_array();
                if ($couopndata) {
                    if ($couopndata["coupon_type"] == "All User") {
                        $couponarray["has_coupon"] = 1;
                        $couponarray["coupon_code"] = $couponcode;
                        $couponarray["coupon_discount"] = $couopndata["value"];
                        $couponarray["coupon_discount_type"] = $couopndata["value_type"];
                        $couponarray["coupon_message"] = $couopndata["promotion_message"];
                    } else {
                        $exitcoupon = $this->db->where("coupon_code", $couponcode)->get("user_order");
                        if ($exitcoupon && $exitcoupon->result_array()) {
                            
                        } else {
                            $couponarray["has_coupon"] = 1;
                            $couponarray["coupon_code"] = $couponcode;
                            $couponarray["coupon_discount"] = $couopndata["value"];
                            $couponarray["coupon_discount_type"] = $couopndata["value_type"];
                            $couponarray["coupon_message"] = $couopndata["promotion_message"];
                        }
                    }
                }
            }
        }
        if ($couponarray["coupon_discount_type"] == "Percent") {
            $dicountvalue = ($total_amount * $couponarray["coupon_discount"]) / 100;
            $couponarray["coupon_discount"] = $dicountvalue;
        }
        $couponarray["coupon_discount"] = number_format($couponarray["coupon_discount"], 2, '.', '');
        $this->response($couponarray);
    }

    //Mobile Booking APi
    function orderFromMobile_post() {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header('Access-Control-Allow-Origin: *');

        $this->config->load('rest', TRUE);
        $bookingarray = $this->post();

        $cartdata = $this->post("cartdata");
        $cartjson = json_decode($cartdata);

        $web_order = array(
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'contact_no' => $this->post('contact_no'),
            'user_id' => $this->post('user_id') ? $this->post('user_id') : 'Guest',
            'zipcode' => $this->post('zipcode') ? $this->post('zipcode') : "",
            'address1' => $this->post('address'),
            'address2' => "",
            'city' => "",
            'state' => "",
            'country' => "",
            'order_date' => date('Y-m-d'),
            'order_time' => date('H:i:s'),
            'amount_in_word' => $this->Order_model->convert_num_word($this->post('total')),
            'sub_total_price' => $this->post('sub_total'),
            'total_price' => $this->post('total'),
            'total_quantity' => $this->post('quantity'),
            'shipping' => "30",
            'status' => 'Order Confirmed',
            'payment_mode' => "Cash on delivery",
            'measurement_style' => '',
            'credit_price' => 0,
            'coupon_code' => $this->post('coupon_code') ? $this->post('coupon_code') : "",
            'discount' => $this->post('discount') ? $this->post('discount') : "",
            'measurement_id' => "",
        );

        $this->db->insert('user_order', $web_order);

        $last_id = $this->db->insert_id();
        $oderid = $last_id;

        $orderno = "AM" . date('Y/m/d') . "/" . $last_id;
        $orderkey = md5($orderno);
        $this->db->set('order_no', $orderno);
        $this->db->set('order_key', $orderkey);
        $this->db->where('id', $last_id);
        $this->db->update('user_order');

        $order_status_data = array(
            'c_date' => date('Y-m-d'),
            'c_time' => date('H:i:s'),
            'order_id' => $last_id,
            'status' => "Order Confirmed",
            'user_id' => $this->post('user_id') ? $this->post('user_id') : 'Guest',
            'remark' => "Order Confirmed By Using COD,  Waiting For Payment",
        );
        $this->db->insert('user_order_status', $order_status_data);

        foreach ($cartjson as $key => $value) {
            $product_dict = array(
                'title' => $value->title,
                'price' => $value->price,
                'sku' => $value->sku,
                'attrs' => "",
                'vendor_id' => "",
                'total_price' => $value->total_price,
                'file_name' => base_url() . 'assets/product_images/' . $value->file_name,
                'quantity' => $value->quantity,
                'user_id' => $value->title,
                'credit_limit' => 0,
                'order_id' => $last_id,
                'product_id' => '',
                'op_date_time' => date('Y-m-d H:i:s'),
            );

            $this->db->insert('cart', $product_dict);
        }
        $this->response(array("order_id" => $oderid));
    }

    //Mobile Booking APi
    function orderFromApp_post() {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header('Access-Control-Allow-Origin: *');

        $this->config->load('rest', TRUE);
        $orderArray = $this->post();

        $cartdata = $this->post("cartdata");

      
        $order_array = array(
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'contact_no' => $this->post('contact_no'),
            'user_id' => $this->post('user_id') ? $this->post('user_id') : 'Guest',
            'zipcode' => $this->post('zipcode') ? $this->post('zipcode') : "",
            'address1' => $this->post('address'),
            'address2' => "",
            'city' => "",
            'state' => "",
            'country' => "",
            'order_date' => date('Y-m-d'),
            'order_time' => date('H:i:s'),
            'amount_in_word' => $this->Order_model->convert_num_word($this->post('total')),
            'sub_total_price' => $this->post('sub_total'),
            'total_price' => $this->post('total'),
            'total_quantity' => $this->post('quantity'),
            'coupon_code' => $this->post('coupon_code') ? $this->post('coupon_code') : "",
            'discount' => $this->post('discount') ? $this->post('discount') : "",
            'shipping' => $this->post('shipping') ? $this->post('shipping') : "30",
            'status' => 'Order Confirmed',
            'payment_mode' => "Cash on delivery",
            'measurement_style' => '',
            'credit_price' => 0,
            'measurement_id' => "",
        );

        $this->db->insert('user_order', $order_array);

        $last_id = $this->db->insert_id();
        $oderid = $last_id;

        $orderno = "AM" . date('Y/m/d') . "/" . $last_id;
        $orderkey = md5($orderno);
        $this->db->set('order_no', $orderno);
        $this->db->set('order_key', $orderkey);
        $this->db->where('id', $last_id);
        $this->db->update('user_order');

        $order_status_data = array(
            'c_date' => date('Y-m-d'),
            'c_time' => date('H:i:s'),
            'order_id' => $last_id,
            'status' => "Order Confirmed",
            'user_id' => $this->post('user_id') ? $this->post('user_id') : 'Guest',
            'remark' => "Order Confirmed By Using COD,  Waiting For Payment",
        );
        $this->db->insert('user_order_status', $order_status_data);

        foreach ($cartdata as $key => $value) {
            $product_dict = array(
                'title' => $value["title"],
                'price' => $value["price"],
                'sku' => $value["sku"],
                'attrs' => "",
                'vendor_id' => "",
                'total_price' => $value["total_price"],
                'file_name' =>  $value["image"],
                'quantity' => $value["quantity"],
                'user_id' => $value["title"],
                'credit_limit' => "0",
                'order_id' => $last_id,
                'product_id' => '',
                'op_date_time' => date('Y-m-d H:i:s'),
            );

            $this->db->insert('cart', $product_dict);
        }
        $this->response(array("order_id" => $oderid));
    }

}

?>