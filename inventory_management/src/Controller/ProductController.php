<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Form\ProductEditType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/view/all', name: 'ad_view_all_products')]
    public function viewAllProduct(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        if ($products == null) {
            $this->addFlash('error', 'Loading products failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        return $this->render(
            'product/index.html.twig',
            [
                'products' => $products
            ]
        );
    }

    #[IsGranted("ROLE_STAFF")]
    #[Route('/staff/view/all', name: 'staff_view_all_products')]
    public function staffViewAllProduct(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        if ($products == null) {
            $this->addFlash('error', 'Loading products failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        return $this->render(
            'product/staff_index.html.twig',
            [
                'products' => $products
            ]
        );
    }

    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/view/detail/{id}', name: 'ad_view_product_detail')]
    public function viewProductDetail($id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Loading product failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        return $this->render(
            'product/detail.html.twig',
            [
                'product' => $product
            ]
        );
    }

    #[IsGranted("ROLE_STAFF")]
    #[Route('/staff/view/detail/{id}', name: 'staff_view_product_detail')]
    public function staffViewVProductDetail($id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Loading product failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        return $this->render(
            'product/staff_detail.html.twig',
            [
                'product' => $product
            ]
        );
    }

    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/delete/{id}', name: 'ad_delete_product')]
    public function bookDelete($id, ManagerRegistry $managerRegistry)
    {
        $product = $managerRegistry->getRepository(Product::class)->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Loading product failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        $manager = $managerRegistry->getManager();
        $manager->remove($product);
        $manager->flush();
        $message = $product->getName() . ' Deleted Successfully';
        $this->addFlash('success', $message);
        return $this->redirectToRoute('ad_view_all_products');
    }

    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/add', name: 'ad_add_product')]
    public function bookAdd(Request $request)
    {
        $product = new Product;
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();
            $this->addFlash('success', 'New Product Added Successfully!');
            return $this->redirectToRoute("ad_view_all_products");
        }
        return $this->renderForm(
            "product/add.html.twig",
            [
                'product_add_form' => $form
            ]
        );
    }

    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/edit/{id}', name: 'ad_edit_product')]
    public function bookEdit($id, Request $request)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Loading product failed!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();
            $this->addFlash('success', 'Product Edited Successfully !');
            return $this->redirectToRoute("ad_view_all_products");
        }
        return $this->renderForm(
            "product/edit.html.twig",
            [
                'product_edit_form' => $form
            ]
        );
    }

    #[IsGranted("ROLE_PRD_ADMIN")]
    #[Route('/admin/raise/product/{id}', name: 'ad_raise_product')]
    public function raiseNumbOfProducts($id, Request $request, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Product not found!');
            return $this->redirectToRoute('ad_view_all_products');
        }
        $numberRaised = $request->get('numb_raised');
        if ($numberRaised == null) {
            $message = 'Raise number of ' . $product->getName() . ' failed!';
            $this->addFlash('error', $message);
            return $this->redirectToRoute("ad_view_all_products");
        }
        $after = $product->getRemain() + $numberRaised;
        $product->setRemain($after);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($product);
        $manager->flush();
        $this->addFlash('success', 'Raised Successfully !');
        return $this->redirectToRoute("ad_view_all_products");
    }

    #[IsGranted("ROLE_STAFF")]
    #[Route('/staff/take/product/{id}', name: 'staff_take_product')]
    public function takeNumbOfProducts($id, Request $request, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if ($product == null) {
            $this->addFlash('error', 'Product not found!');
            return $this->redirectToRoute('staff_view_all_products');
        }
        $numberTaken = $request->get('numb_taken');
        if ($numberTaken == null) {
            $message = 'Take ' . $product->getName() . ' failed!';
            $this->addFlash('error', $message);
            return $this->redirectToRoute("staff_view_all_products");
        }
        $after = $product->getRemain() - $numberTaken;
        $product->setRemain($after);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($product);
        $manager->flush();
        $this->addFlash('success', 'Taken Successfully !');
        return $this->redirectToRoute("staff_view_all_products");
    }
}