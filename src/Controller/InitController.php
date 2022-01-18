<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\CartProduct;
use App\Entity\Cart;
use App\Entity\Order;

class InitController extends AbstractController
{

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine){
      $this->doctrine = $doctrine;
      $this->entityManager = $doctrine->getManager();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //        ROUTES
    //
    ////////////////////////////////////////////////////////////////////////////


    #[Route('/init', name: 'init')]
    public function index(): JsonResponse
    {
      $status = "OK";

      try{
        $xml = $this->XMLToString();

        foreach($xml->order as $order){
          $this->createOrder($order);
        }

        $this->entityManager->flush();

      }catch(\Exception $e){
        $status = "ERROR - ".$e->getMessage();
      }

      return new JsonResponse(
        array(
          "status"=>$status
        )
      );
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //        PRIVATE METHODS
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    private function XMLToString(){
      $filepath = __DIR__."/../../public/assets/orders.xml";
      $myXMLData = file_get_contents($filepath);
      $xml = simplexml_load_string($myXMLData) or die("Error: Cannot create object");

      return $xml;
    }


    ////////////////////////////////////////////////////////////////////////////
    //
    //        ENTITY BUILDERS
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \App\Entity\Customer
     */
    private function createCustomer($customer){

      $existing = $this->doctrine->getRepository(Customer::class)->find($customer["id"]);

      if($existing) return $existing;

      $dbCustomer = new Customer();
      $dbCustomer->setId(intval($customer["id"]));
      $dbCustomer->setFirstname($customer->firstname);
      $dbCustomer->setLastname($customer->lastname);

      $this->entityManager->persist($dbCustomer);
      // $this->entityManager->flush();

      return $dbCustomer;
    }

    /**
     * @return \App\Entity\Product
     */
    private function createProduct($product){
      $existing = $this->doctrine->getRepository(Product::class)->find($product["sku"]);

      if($existing) return $existing;

      $dbProduct = new Product();
      $dbProduct->setSku($product["sku"]);
      $dbProduct->setName($product->name);
      $dbProduct->setPrice(floatval($product->price));

      $this->entityManager->persist($dbProduct);
      // $this->entityManager->flush();

      return $dbProduct;
    }

    /**
     * @return \App\Entity\CartProduct
     */
    private function createCartProduct($cartProduct){


      $product = $this->createProduct($cartProduct);

      $dbCartProduct = new CartProduct();

      $dbCartProduct->setProduct($product);
      $dbCartProduct->setQuantity(intval($cartProduct->quantity));

      $this->entityManager->persist($dbCartProduct);
      // $this->entityManager->flush();

      return $dbCartProduct;
    }

    /**
     * @return \App\Entity\Cart
     */
    private function createCart($cart){
      $dbCart = new Cart();

      foreach($cart->product as $cartProduct){
        $dbCartProduct = $this->createCartProduct($cartProduct);
        $dbCart->addCartProduct($dbCartProduct);
      }

      $this->entityManager->persist($dbCart);
      // $this->entityManager->flush();

      return $dbCart;
    }

    /**
     * @return \App\Entity\Order
     */
    private function createOrder($order){
      $existing = $this->doctrine->getRepository(Order::class)->find($order["id"]);

      if($existing) return $existing;

      $dbOrder = new Order();

      $dbOrder->setId($order["id"]);
      $dbOrder->setOrderDate(new \DateTime($order->orderDate));
      $dbOrder->setStatus($order->status);
      $dbOrder->setPrice(floatval($order->price));

      $customer = $this->createCustomer($order->customer);
      $dbOrder->setCustomer($customer);

      $cart = $this->createCart($order->cart);
      $dbOrder->setCart($cart);


      $this->entityManager->persist($dbOrder);
      // $this->entityManager->flush();

      return $dbOrder;
    }
}
