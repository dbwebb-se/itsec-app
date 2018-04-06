<?php

namespace Course\Cart;

use \Anax\Configure\ConfigureInterface;
use \Anax\Configure\ConfigureTrait;
use \Anax\DI\InjectionAwareInterface;
use \Anax\Di\InjectionAwareTrait;

use \Course\Order\OrderItem;
use \Course\Order\Orders;
use \Course\Product\Product;
use \Course\User\User;
use \Course\Coupon\Coupon;

class CartController implements
    ConfigureInterface,
    InjectionAwareInterface
{
    use ConfigureTrait,
    InjectionAwareTrait;


    public function displayCart() {
        $title = "Kundvagn";
        $view = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        #Get current session.
        $session = $this->di->get("session");

        $data = [
            "cartItems" => $session->get("items"),
        ];

        $view->add("cart/cart", $data);
        $pageRender->renderPage(["title" => $title]);
    }



    public function displayCheckout() {
        $title = "Kassa";
        $view = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        #Get current session.
        $session = $this->di->get("session");
        $session->set("order", true);
        $session->set("orderID", null);

        $data = [
            "cartItems" => $session->get("items"),
        ];

        $view->add("cart/checkout", $data);
        $pageRender->renderPage(["title" => $title]);
    }



    public function displayOrder() {
        $title = "Beställning lagd";
        $view = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");
        $db = $this->di->get("db");

        $session = $this->di->get("session");

        $user = new User();
        $user->setDb($db);
        $user->getUserInformationByEmail($session->get("email"));

        if ($session->get("order") == true) {
            if ($this->di->get("request")->getPost("coupon") != null) {
                $coupon = new Coupon();
                $code = $this->di->get("request")->getPost("coupon", null);
                $coupon->setDb($db);
                $couponData = $coupon->validateCoupon($code);
            }

            $order = new Orders();
            $order->setDb($db);
            $order->setUserID($user->userID);

            if (isset($couponData)) {
                $order->setCouponID($couponData->couponID);
            }

            $order->save();

            $orderID = $order->getLastInsertedID();
            $session->set("orderID", $orderID);

            foreach ((array)$session->get("items") as $value) {
                $product = new Product();
                $product->setDb($db);
                $product->getProductByID($value['productID']);
                $product->productAmount = ($product->productAmount - (int)$value["amount"]);
                $product->save();

                $orderItem = new OrderItem();
                $orderItem->setDb($db);
                $orderItem->setOrderID((int)$orderID);
                $orderItem->setProductID((int)$value["productID"]);
                $orderItem->setProductAmount((int)$value["amount"]);
                $orderItem->save();
            }

            $this->di->get("session")->set("order", false);
        }

        $data = [
            "userInfo" => $user,
            "orderID" => $session->get("orderID"),
            "cartItems" => $session->get("items"),
        ];

        $session->delete("items");
        $session->delete("orderID");

        $view->add("cart/order", $data);
        $pageRender->renderPage(["title" => $title]);
    }
}
