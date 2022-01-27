<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use Doctrine\ORM\Query\AST\DeleteClause;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractController
{

    /**
     * @Route("cart/add/{id}", name="add_cart")
     */
    public function addCart($id, SessionInterface $sessionInterface)
    {
        $cart = $sessionInterface->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute('product_show', ['id' => $id]);
    }

    /**
     * @Route("cart", name="show_cart")
     */
    public function showCart(SessionInterface $sessionInterface, ProductRepository $productRepository)
    {

        $cart = $sessionInterface->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $this->render('front/cart.html.twig', ['cartProducts' => $cartWithData]);
    }

    /**
     * @Route("cart/delete/{id}", name="delete_cart")
     */
    public function DeleteCart($id, SessionInterface $sessionInterface)
    {
        $cart = $sessionInterface->get('cart', []);

        if (!empty($cart[$id] && $cart[$id] == 1)) {
            unset($cart[$id]);
        } else {
            $cart[$id]--;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute("show_cart");
    }
}
