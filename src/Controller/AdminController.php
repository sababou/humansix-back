<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\CartProduct;
use App\Entity\Cart;
use App\Entity\Order;


class AdminController extends AbstractController
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


    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $cookie = $request->cookies->has('is_logged') ? $request->cookies->get('is_logged') : null ;
        if($cookie != "yes"){
          return $this->redirectToRoute('login');
        }

        $orderBy = $request->query->has('order_by') ? $request->query->get('order_by') : "ASC";

        $orders = $this->doctrine->getRepository(Order::class)->findBy([], ["orderDate" => $orderBy]);
        $products = $this->doctrine->getRepository(Product::class)->findBy([]);

        return $this->render('admin/index.html.twig', [
            'orders' => $orders,
            'products' => $products,
            'orderBy' => $orderBy
        ]);
    }

    #[Route('/add_order', name: 'admin_add_order', methods: ['POST'])]
    public function addOrder(Request $request): Response
    {
        $cookie = $request->cookies->has('is_logged') ? $request->cookies->get('is_logged') : null ;
        if($cookie != "yes"){
          return $this->redirectToRoute('login');
        }

        $customerFirstname = $request->request->has('customer_firstname') ? $request->request->get('customer_firstname') : null;
        $customerLastname = $request->request->has('customer_lastname') ? $request->request->get('customer_lastname') : null;

        if(empty($customerFirstname) || empty($customerLastname)){
          $this->addFlash('warning', 'Informations client incorrectes');
          return $this->redirectToRoute('home');
        }

        $productsQuantity = array();
        $products = $this->doctrine->getRepository(Product::class)->findBy([]);

        foreach($products as $product){
          $quantity = $request->request->has('quantity_product_'.$product->getSku()) ? $request->request->get('quantity_product_'.$product->getSku()) : null;

          if(!empty($quantity)){
            $productsQuantity[] = array(
              "product" => $product,
              "quantity" => intval($quantity)
            );
          }
        }

        if(! (count($productsQuantity) > 0)){
          $this->addFlash('warning', 'Quantité produits incorrecte');
          return $this->redirectToRoute('home');
        }

        $totalPrice = 0;

        $customer = new Customer();
        $customer->setFirstname($customerFirstname);
        $customer->setLastname($customerLastname);

        $this->entityManager->persist($customer);

        $cart = new Cart();

        foreach($productsQuantity as $temp){
          $cartProduct = new CartProduct();
          $cartProduct->setProduct($temp["product"]);
          $cartProduct->setQuantity($temp["quantity"]);

          $cart->addCartProduct($cartProduct);

          $totalPrice += $product->getPrice() * $temp["quantity"] ;

          $this->entityManager->persist($cartProduct);
        }

        $this->entityManager->persist($cart);

        $order = new Order();

        $order->setCustomer($customer);
        $order->setCart($cart);
        $order->setPrice($totalPrice);
        $order->setOrderDate(new \DateTime());
        $order->setStatus("processing");

        $this->entityManager->persist($order);

        $this->entityManager->flush();

        $this->addFlash('success', 'Commande ajoutée avec succès.');

        return $this->redirectToRoute('home');
    }

    #[Route('/login', name: 'login')]
    public function login(Request $request): Response
    {
        $username = $request->request->has('username') ? $request->request->get('username') : null ;
        $password = $request->request->has('password') ? $request->request->get('password') : null ;

        if($username == "admin" && $password == "S3cr3T+"){

          $cookie = Cookie::create('is_logged')
            ->withValue('yes')
            ->withExpires(strtotime("+10 minutes"));

          $response = new RedirectResponse($this->generateUrl('home'));
          $response->headers->setCookie($cookie);

          return $response;
        }

        return $this->render('admin/login.html.twig', []);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $cookie = Cookie::create('is_logged')
          ->withValue('no')
          ->withExpires(strtotime("-10 minutes"));


        $response = new RedirectResponse($this->generateUrl('login'));
        $response->headers->setCookie($cookie);

        return $response;
    }



}
