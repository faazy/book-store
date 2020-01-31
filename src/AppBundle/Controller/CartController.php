<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use AppBundle\Util;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/cart")
 */
class CartController extends Controller
{

    const FICTION = "Fiction";
    const CHILDREN = "Children";

    private $util;

    public function getUtil()
    {
        if (is_null($this->util)) {
            $this->util = new Util();
        }
        return $this->util;
    }

    /**
     * @Route("/book/{id}",name="cart_item")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function addToCart(Request $request, $id)
    {
        $session = $request->getSession();
        /** @var Book $book */
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

        if (!$session->has("cartItems")) {
            $newCart = [];
            $session->set("cartItems", $newCart);
        }

        $cartItems = $session->get("cartItems");

        if (!array_key_exists($id, $cartItems['items'])) {
            $cartItems["items"][$id] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'quantity' => 1,
                'category' => $book->getCategory()->getName(),
                'description' => $book->getDescription(),
                'unitPrice' => $book->getUnitPrice(),
                'sub_total' => $book->getUnitPrice() * 1,
            ];
        } else {
            $cartItems["items"][$id]['quantity'] += 1;
        }

        $cartItems = $this->getUtil()->calculateTotal($cartItems);
        $session->set("cartItems", $cartItems);

        return $this->redirectToRoute('cart_view');
    }

    /**
     * @Route("/view",name="cart_view")
     * @param Request $request
     * @return Response
     */
    public function viewCart(Request $request)
    {
        $session = $request->getSession();
        $cartItemCount = 0;
        if (!$session->has("cartItems")) {
            $newCart = array();
            $session->set("cartItems", $newCart);
        } else {
            $cartItems = $session->get("cartItems");
            $cartItemCount = count($cartItems["items"]);
        }

        return $this->render('application/cart/checkout.html.twig', array(
            "cart" => $session->get("cartItems"),
            "cartItemCount" => $cartItemCount
        ));
    }

    /**
     * @Route("/delete/{id}",name="remove_cart_item")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function removeFromCart(Request $request, $id)
    {
        $session = $request->getSession();

        if ($session->has("cartItems")) {
            $cartItems = $session->get("cartItems");

            if (array_key_exists($id, $cartItems['items'])) {
                unset($cartItems["items"][$id]);
                $cartItems = $this->getUtil()->calculateTotal($cartItems);
                $session->set("cartItems", $cartItems);
            }
        }

        return $this->redirectToRoute('cart_view');
    }

    /**
     * @Route("/update", name="update_cart_item")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $session = $request->getSession();
        $data = $request->request->all();
        $cartItems = [];

        if ($session->has("cartItems")) {
            $cartItems = $session->get("cartItems");

            foreach ($data['quantity'] as $id => $qty) {
                //Update the Item Quantity
                foreach ($cartItems['items'] as &$item) {
                    if ($item['id'] == $id) {
                        $item['quantity'] = $qty;
                        $item['sub_total'] = $item['unitPrice'] * $qty;
                        break;
                    }
                }
            }

            $cartItems = $this->getUtil()->calculateTotal($cartItems);
            $session->set("cartItems", $cartItems);
        }

        return $this->json($cartItems);
    }


    /**
     *
     * @Route("/checkout",name="checkout_cart")
     * @param Request $request
     * @return Response
     */
    public function checkOut(Request $request)
    {
        $isCouponCodeEnabled = false;
        $session = $request->getSession();
        $cartItems = $session->get("cartItems");
        $invoice = [];
        $fictionTotal = 0;
        $childrenTotal = 0;

        foreach ($cartItems["items"] as $cartItem) {
            $invoice[Util::FICTION]["subTotal"] = 0;
            $invoice[Util::FICTION]["items"] = [];
            $invoice[Util::FICTION]["discount"] = 0;

            if ($cartItem["category"] == Util::FICTION) {
                $fictionTotal += $cartItem['quantity'] * $cartItem["unitPrice"];
                $invoice[Util::FICTION]["items"][] = $cartItem;
                $invoice[Util::FICTION]["subTotal"] = $fictionTotal;
                $invoice[Util::FICTION]["discount"] = 0;

            }

            if ($cartItem["category"] == Util::CHILDREN) {
                $childrenTotal += $cartItem['quantity'] * $cartItem["unitPrice"];
                $invoice[Util::CHILDREN]["items"][] = $cartItem;
                $invoice[Util::CHILDREN]["subTotal"] = $childrenTotal;
                $invoice[Util::CHILDREN]["discount"] = 0;

            }
        }

        $invoice["total"] = $invoice[Util::CHILDREN]["subTotal"] + $invoice[Util::FICTION]["subTotal"];
        $request->request->has("coupon_code");

        if (strlen($request->request->get("coupon_code")) > 0) {
            $isCouponCodeEnabled = true;
        }

        $invoice = $this->getUtil()->calculateDiscount($invoice, $isCouponCodeEnabled);

        return $this->render('application/cart/invoice.html.twig', [
            "invoice" => $invoice,
        ]);
    }
}
