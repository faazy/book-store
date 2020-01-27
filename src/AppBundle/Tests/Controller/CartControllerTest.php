<?php
/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 8/19/18
 * Time: 8:57 PM
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\CartController;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CartControllerTest extends TestCase
{
    public function testAdd()
    {
        $cartController = new CartController();
        $result = $cartController->calculateTotal(array());
        var_dump($result);
        // assert that your calculator added the numbers correctly!
    }

}