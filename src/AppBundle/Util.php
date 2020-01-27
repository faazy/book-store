<?php
/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 8/19/18
 * Time: 9:17 PM
 */

namespace AppBundle;


class Util
{
    const FICTION = "Fiction";
    const CHILDREN = "Children";

    /**
     * @param $invoice
     * @param $couponStatus
     * @return mixed
     */
    public function calculateDiscount($invoice, $couponStatus)
    {
        $invoice["discount"] = 0;
        $invoice["total"] = 0;

        if ($couponStatus) {
            $invoice["discount"] = ($invoice["total"] * 10 / 100);
            $newTotal = $invoice["total"] - $invoice["discount"];
            $invoice["total"] = $newTotal;
        } else {
            $childrenBookCount = count($invoice[Util::CHILDREN]["items"]);
            $fictionBookCount = count($invoice[Util::FICTION]["items"]);

            if ($childrenBookCount >= 5) {
                $childrenTotal = $invoice[Util::CHILDREN]["subTotal"];
                $childrenDiscount = $childrenTotal * 5 / 100;
                $childrenDiscountedTotal = $childrenTotal - $childrenDiscount;
                $invoice[Util::CHILDREN]["subTotal"] = $childrenDiscountedTotal;
                $invoice[Util::CHILDREN]["discount"] = $childrenDiscount;
            }

            $invoice["total"] = $invoice[Util::CHILDREN]["subTotal"] + $invoice[Util::FICTION]["subTotal"];

            if ($fictionBookCount >= 10 && $childrenBookCount >= 10) {
                $invoice["discount"] = ($invoice["total"] * 5 / 100);
                $newTotal = $invoice["total"] - $invoice["discount"];
                $invoice["total"] = $newTotal;
            }
        }

        return $invoice;
    }

    public function calculateTotal($cartItems)
    {
        $cartTotal = 0;
        foreach ($cartItems["items"] as $cartItem) {
            $cartTotal += $cartItem['quantity'] * $cartItem['unitPrice'];
        }
        $cartItems["cartTotal"] = $cartTotal;
        return $cartItems;
    }
}
