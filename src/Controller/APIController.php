<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Product;
use App\Entity\Order;


#[Route('/api')]
class APIController extends AbstractController
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

    #[Route('/orders', name: 'api_orders')]
    public function orders(Request $request): JsonResponse
    {
        $orderBy = $request->query->has('order_by') ? $request->query->has('order_by') : "ASC";

        $dbOrders = $this->doctrine->getRepository(Order::class)->findBy([], ["orderDate" => $orderBy]);

        $orders = array();

        foreach($dbOrders as $order){
          $orders[] = $order->buildArray();
        }

        return new JsonResponse(
          array(
            "status" => "OK",
            "orders" => $orders
          )
        );
    }

    #[Route('/order/{id}', name: 'api_order')]
    public function order(Request $request, $id): JsonResponse
    {

      $order = $this->doctrine->getRepository(Order::class)->find($id);

      $orderArray = $order ? $order->buildArray() : [];

      return new JsonResponse(
        array(
          "status" => "OK",
          "order" => $orderArray
        )
      );
    }

    #[Route('/products', name: 'api_products')]
    public function products(): JsonResponse
    {

        $dbProducts = $this->doctrine->getRepository(Product::class)->findBy([]);

        $products = array();

        foreach($dbProducts as $product){
          $products[] = $product->buildArray();
        }

        return new JsonResponse(
          array(
            "status" => "OK",
            "products" => $products
          )
        );
    }

    #[Route('/product/{sku}', name: 'api_product')]
    public function product(Request $request, $sku): JsonResponse
    {
      $product = $this->doctrine->getRepository(Product::class)->find($sku);

      $productArray = $product ? $product->buildArray() : [];

      return new JsonResponse(
        array(
          "status" => "OK",
          "product" => $productArray
        )
      );
    }
}
